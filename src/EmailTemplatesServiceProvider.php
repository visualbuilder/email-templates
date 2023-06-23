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

    /**
     * Bootstrap the application services.
     */
    // public function boot()
    // {
    //     /*
    //      * Optional methods to load your package assets
    //      */
    //     // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'email-templates');
    //     // $this->loadViewsFrom(__DIR__.'/../resources/views', 'email-templates');
    //     // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    //     // $this->loadRoutesFrom(__DIR__.'/routes.php');

    //     if ($this->app->runningInConsole()) {
    //         $this->publishes([
    //             __DIR__.'/../config/config.php' => config_path('email-templates.php'),
    //         ], 'config');

    //         // Publishing the views.
    //         /*$this->publishes([
    //             __DIR__.'/../resources/views' => resource_path('views/vendor/email-templates'),
    //         ], 'views');*/

    //         // Publishing assets.
    //         /*$this->publishes([
    //             __DIR__.'/../resources/assets' => public_path('vendor/email-templates'),
    //         ], 'assets');*/

    //         // Publishing the translation files.
    //         /*$this->publishes([
    //             __DIR__.'/../resources/lang' => resource_path('lang/vendor/email-templates'),
    //         ], 'lang');*/

    //         // Registering package commands.
    //         // $this->commands([]);
    //     }
        
    //     // $this->getResources();
    //     // $this->getMigrations();
    // }

    /**
     * Register the application services.
     */
    // public function register()
    // {
    //     // Automatically apply the package configuration
    //     $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'email-templates');

    //     // Register the main class to use with the facade
    //     $this->app->singleton('email-templates', function () {
    //         return new EmailTemplates;
    //     });
    // }

    

    public function configurePackage(Package $package): void
    {
        $package->name("email-template-filament-plugin")
            ->hasMigrations(['create_email_templates_table'])
            ->hasConfigFile('email-templates');

    }
}
