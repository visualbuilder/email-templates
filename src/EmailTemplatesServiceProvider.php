<?php

namespace Visualbuilder\EmailTemplates;

use Filament\PluginServiceProvider;
use Spatie\LaravelPackageTools\Package;
use Visualbuilder\EmailTemplates\Resources\EmailTemplateResource;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Request;

class EmailTemplatesServiceProvider extends PluginServiceProvider
{

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

    public function boot()
    {
        parent::boot();
        if ($this->app->runningInConsole()) {
            $this->publishResources();
        }
    }

    protected function publishResources()
    {
        $this->publishes([__DIR__ . '/../database/seeders/EmailTemplateSeeder.php' => database_path('seeders/EmailTemplateSeeder.php'),
                         ], 'email-template-filament-plugin-seeds');
    }

}
