<?php

namespace Visualbuilder\EmailTemplates;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Visualbuilder\EmailTemplates\Resources\EmailTemplateResource;

class EmailTemplatesPlugin implements Plugin
{
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

    public function register(Panel $panel): void
    {
        if(! config('email-templates.publish_resource')) {
            $panel->resources([
                EmailTemplateResource::class,
            ]);
        }
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
