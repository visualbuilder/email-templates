<?php

namespace Visualbuilder\EmailTemplates;

use Closure;
use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\Support\Concerns\EvaluatesClosures;
use Visualbuilder\EmailTemplates\Resources\EmailTemplateResource;
use Visualbuilder\EmailTemplates\Resources\EmailTemplateThemeResource;

class EmailTemplatesPlugin implements Plugin
{
    use EvaluatesClosures;

    protected string|Closure|null $navigationGroup = null;

    protected bool|Closure|null $navigation = null;

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
        return $this->evaluate($this->navigation) ?? config('filament-email-templates.navigation.enabled',true);
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
