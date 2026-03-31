<?php

namespace Visualbuilder\EmailTemplates;

use Closure;
use Filament\Contracts\Plugin;
use Filament\Facades\Filament;
use Filament\Panel;
use Filament\Support\Concerns\EvaluatesClosures;
use Illuminate\Support\Str;
use Visualbuilder\EmailTemplates\Resources\EmailTemplateResource;
use Visualbuilder\EmailTemplates\Resources\EmailTemplateThemeResource;

class EmailTemplatesPlugin implements Plugin
{
    use EvaluatesClosures;

    protected string|Closure|null $navigationGroup = null;

    protected bool|Closure|null $navigation = null;

    protected ?Closure $screenshotCaptureCallback = null;

    protected bool|Closure $multitenancy = false;

    protected ?string $tenantModel = null;

    protected ?string $tenantForeignKey = null;

    protected ?string $ownershipRelationship = null;

    /**
     * Configure a callback to capture screenshots of email themes.
     * The callback receives HTML string and should return ['image' => binary, 'contentType' => 'image/png'] or null.
     */
    public function screenshotCapture(Closure $callback): static
    {
        $this->screenshotCaptureCallback = $callback;

        return $this;
    }

    public function getScreenshotCaptureCallback(): ?Closure
    {
        return $this->screenshotCaptureCallback;
    }

    public function hasScreenshotCapture(): bool
    {
        return $this->screenshotCaptureCallback !== null;
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        return filament(app(static::class)->getId());
    }

    public function getId(): string
    {
        return 'filament-email-templates';
    }

    public function enableNavigation(bool|Closure $callback = true): static
    {
        $this->navigation = $callback;

        return $this;
    }

    public function shouldRegisterNavigation(): bool
    {
        return $this->evaluate($this->navigation) ?? config('filament-email-templates.navigation.enabled', true);
    }

    public function navigationGroup(string|Closure|null $navigationGroup): static
    {
        $this->navigationGroup = $navigationGroup;
        return $this;
    }

    public function getNavigationGroup(): ?string
    {
        return $this->evaluate($this->navigationGroup) ?? config('filament-email-templates.navigation.templates.group');
    }

    // ── Multitenancy ────────────────────────────────────────────────

    public function multitenancy(bool|Closure $enabled = true): static
    {
        $this->multitenancy = $enabled;

        return $this;
    }

    public function tenantModel(string $model): static
    {
        $this->tenantModel = $model;

        return $this;
    }

    public function tenantForeignKey(string $key): static
    {
        $this->tenantForeignKey = $key;

        return $this;
    }

    public function ownershipRelationship(string $relationship): static
    {
        $this->ownershipRelationship = $relationship;

        return $this;
    }

    public function isMultitenancyEnabled(): bool
    {
        return $this->evaluate($this->multitenancy)
            || config('filament-email-templates.multitenancy.enabled', false);
    }

    public function getTenantModel(): ?string
    {
        return $this->tenantModel
            ?? config('filament-email-templates.multitenancy.tenant_model')
            ?? (class_exists(Filament::class) ? Filament::getTenantModel() : null);
    }

    public function getTenantForeignKey(): string
    {
        if ($this->tenantForeignKey) {
            return $this->tenantForeignKey;
        }

        if ($key = config('filament-email-templates.multitenancy.tenant_foreign_key')) {
            return $key;
        }

        $model = $this->getTenantModel();

        return $model ? Str::snake(class_basename($model)) . '_id' : 'tenant_id';
    }

    public function getOwnershipRelationship(): string
    {
        if ($this->ownershipRelationship) {
            return $this->ownershipRelationship;
        }

        if ($rel = config('filament-email-templates.multitenancy.ownership_relationship')) {
            return $rel;
        }

        $model = $this->getTenantModel();

        return $model ? Str::camel(class_basename($model)) : 'tenant';
    }

    // ── Panel registration ──────────────────────────────────────────

    public function register(Panel $panel): void
    {
        $panel->resources([
            EmailTemplateResource::class,
            EmailTemplateThemeResource::class,
        ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
