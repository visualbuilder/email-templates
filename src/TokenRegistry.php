<?php

namespace Visualbuilder\EmailTemplates;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Enumerable catalogue of every token available to template authors.
 *
 * DefaultTokenHelper remains the render-time resolver; this registry is the
 * design-time catalogue the editor UX consumes so authors can browse and
 * insert tokens instead of guessing them.
 *
 * Sources (all optional, all merged):
 * - config('filament-email-templates.recipients')      — notifiable models, keyed by camel class basename
 * - config('filament-email-templates.preview_models')  — token prefix => model class
 * - config('filament-email-templates.token_models')    — key => class, or key => ['class' => ..., 'attributes' => [...], 'label' => ...]
 * - config('filament-email-templates.config_keys')     — whitelisted ##config.x## keys
 * - config('filament-email-templates.known_tokens')    — singular tokens like ##tokenUrl##
 * - runtime registrations via registerModel() / registerConfigKeys() / registerToken()
 */
class TokenRegistry
{
    /** @var array<string, array{class: class-string<Model>, attributes: array<int, string>|null, label: string|null}> */
    protected array $models = [];

    /** @var array<int, string> */
    protected array $configKeys = [];

    /** @var array<string, array<string, string>> group label => [token => label] */
    protected array $customTokens = [];

    /**
     * Register a model whose attributes can be used as ##key.attribute## tokens.
     * Pass an explicit attribute list, or omit it to derive one from the
     * model's fillable + appended attributes (minus hidden).
     */
    public function registerModel(string $key, string $class, ?array $attributes = null, ?string $label = null): static
    {
        $this->models[$key] = [
            'class' => $class,
            'attributes' => $attributes,
            'label' => $label,
        ];

        return $this;
    }

    public function registerConfigKeys(array $keys): static
    {
        $this->configKeys = array_merge($this->configKeys, $keys);

        return $this;
    }

    public function registerToken(string $token, ?string $label = null, string $group = 'General'): static
    {
        $this->customTokens[$group][$token] = $label ?? trim($token, '#');

        return $this;
    }

    /**
     * All tokens grouped by source, groups and entries sorted alphabetically.
     *
     * @return Collection<string, Collection<int, array{token: string, label: string}>>
     */
    public function groups(): Collection
    {
        $groups = collect();

        foreach ($this->allModels() as $key => $definition) {
            $label = $definition['label'] ?? Str::headline($key);

            $entries = collect($this->attributesFor($definition))
                ->sort()
                ->values()
                ->map(fn (string $attribute) => [
                    'token' => "##{$key}.{$attribute}##",
                    'label' => $attribute,
                ]);

            if ($entries->isNotEmpty()) {
                $groups->put($label, $entries);
            }
        }

        $configEntries = collect($this->allConfigKeys())
            ->unique()
            ->sort()
            ->values()
            ->map(fn (string $key) => [
                'token' => "##config.{$key}##",
                'label' => $key,
            ]);

        if ($configEntries->isNotEmpty()) {
            $groups->put('Config', $configEntries);
        }

        foreach ($this->allCustomTokens() as $group => $tokens) {
            $entries = collect($tokens)
                ->map(fn (string $label, string $token) => ['token' => $token, 'label' => $label])
                ->sortBy('label')
                ->values();

            $groups->put(
                $group,
                $groups->get($group, collect())->concat($entries)->sortBy('label')->values()
            );
        }

        return $groups->sortKeys();
    }

    /**
     * Flat, alphabetically sorted list of every token.
     *
     * @return Collection<int, array{token: string, label: string, group: string}>
     */
    public function tokens(): Collection
    {
        return $this->groups()
            ->flatMap(fn (Collection $entries, string $group) => $entries
                ->map(fn (array $entry) => $entry + ['group' => $group]))
            ->sortBy('token')
            ->values();
    }

    /**
     * Payload shape consumed by the editor's Insert Token control.
     *
     * @return array<int, array{label: string, items: array<int, array{token: string, label: string}>}>
     */
    public function menu(): array
    {
        return $this->groups()
            ->map(fn (Collection $entries, string $label) => [
                'label' => $label,
                'items' => $entries->values()->all(),
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<string, array{class: class-string<Model>|null, attributes: array<int, string>|null, label: string|null}>
     */
    protected function allModels(): array
    {
        $models = [];

        foreach ((array) config('filament-email-templates.recipients', []) as $class) {
            $models[Str::camel(class_basename($class))] = ['class' => $class, 'attributes' => null, 'label' => null];
        }

        foreach ((array) config('filament-email-templates.preview_models', []) as $key => $class) {
            $models[$key] = ['class' => $class, 'attributes' => null, 'label' => null];
        }

        foreach ((array) config('filament-email-templates.token_models', []) as $key => $definition) {
            $models[$key] = is_array($definition)
                ? [
                    'class' => $definition['class'] ?? null,
                    'attributes' => $definition['attributes'] ?? null,
                    'label' => $definition['label'] ?? null,
                ]
                : ['class' => $definition, 'attributes' => null, 'label' => null];
        }

        return array_merge($models, $this->models);
    }

    /**
     * @return array<int, string>
     */
    protected function attributesFor(array $definition): array
    {
        if ($definition['attributes'] !== null) {
            return $definition['attributes'];
        }

        $class = $definition['class'];

        if (! $class || ! class_exists($class) || ! is_subclass_of($class, Model::class)) {
            return [];
        }

        /** @var Model $model */
        $model = new $class;

        return array_values(array_diff(
            array_unique(array_merge($model->getFillable(), $model->getAppends())),
            $model->getHidden(),
            $this->excludedAttributes()
        ));
    }

    /**
     * Secrets that must never surface in the token catalogue, even when a
     * model forgets to hide them. Applies only to derived attribute lists;
     * an explicit attribute list is taken as intentional.
     *
     * @return array<int, string>
     */
    protected function excludedAttributes(): array
    {
        return (array) config('filament-email-templates.token_excluded_attributes', [
            'password',
            'password_confirmation',
            'remember_token',
            'api_token',
            'two_factor_secret',
            'two_factor_recovery_codes',
        ]);
    }

    /**
     * @return array<int, string>
     */
    protected function allConfigKeys(): array
    {
        return array_merge(
            (array) config('filament-email-templates.config_keys', []),
            $this->configKeys
        );
    }

    /**
     * @return array<string, array<string, string>>
     */
    protected function allCustomTokens(): array
    {
        $tokens = $this->customTokens;

        foreach ((array) config('filament-email-templates.known_tokens', []) as $key) {
            $tokens['General']["##{$key}##"] ??= $key;
        }

        return $tokens;
    }
}
