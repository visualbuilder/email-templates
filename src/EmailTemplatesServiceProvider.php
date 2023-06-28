<?php

namespace Visualbuilder\EmailTemplates;

use Filament\PluginServiceProvider;
use Spatie\LaravelPackageTools\Package;
use Visualbuilder\EmailTemplates\Resources\EmailTemplateResource;


class EmailTemplatesServiceProvider extends PluginServiceProvider
{
    // public static string $name = "email-template-filament-plugin";

    protected array $resources = [
        EmailTemplateResource::class,
    ];
    

    public function configurePackage(Package $package): void
    {
        $package->name("email-template-filament-plugin")
            ->hasMigrations(['create_email_templates_table'])
            ->hasConfigFile('email-templates')
            ->hasViews('visual-builder/email-templates')
            ->runsMigrations();

    }
}
