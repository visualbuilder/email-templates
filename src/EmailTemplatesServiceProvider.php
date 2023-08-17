<?php

namespace Visualbuilder\EmailTemplates;

use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Support\Facades\Route;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Visualbuilder\EmailTemplates\Commands\InstallCommand;
use Visualbuilder\EmailTemplates\Commands\PublishEmailTemplateResource;
use Visualbuilder\EmailTemplates\Contracts\CreateMailableInterface;
use Visualbuilder\EmailTemplates\Contracts\TokenHelperInterface;
use Visualbuilder\EmailTemplates\Helpers\CreateMailableHelper;
use Visualbuilder\EmailTemplates\Helpers\TokenHelper;
use Visualbuilder\EmailTemplates\Http\Controllers\EmailTemplateController;
use Visualbuilder\EmailTemplates\Resources\EmailTemplateResource;

class EmailTemplatesServiceProvider extends PackageServiceProvider
{
    // protected array $resources = [
    //     EmailTemplateResource::class,
    // ];

    // protected array $styles = [
    //     'vb-email-templates-styles' => 'https://cdn.jsdelivr.net/gh/lipis/flag-icons@6.6.6/css/flag-icons.min.css',
    // ];

    public function configurePackage(Package $package): void
    {
        $package->name("filament-email-templates")
            ->hasMigrations(['create_email_templates_table'])
            ->hasConfigFile(['email-templates', 'filament-tiptap-editor'])
            ->hasAssets()
            ->hasViews('vb-email-templates')
            ->runsMigrations()
            ->hasCommands([
                InstallCommand::class,
                PublishEmailTemplateResource::class,
            ]);
    }

    // public function register()
    // {
    //     parent::register();
    //     $this->app->singleton(TokenHelperInterface::class, TokenHelper::class);
    //     $this->app->singleton(CreateMailableInterface::class, CreateMailableHelper::class);
    //     $this->app->register(EmailTemplatesEventServiceProvider::class);
    // }

    public function packageRegistered(): void
    {
        parent::packageRegistered();

        $this->app->singleton(TokenHelperInterface::class, TokenHelper::class);
        $this->app->singleton(CreateMailableInterface::class, CreateMailableHelper::class);
        $this->app->register(EmailTemplatesEventServiceProvider::class);
    }

    // public function boot()
    // {
    //     parent::boot();
    //     if($this->app->runningInConsole()) {
    //         $this->publishResources();
    //     }

    //     $this->registerRoutes();

    //     $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'vb-email-templates');
    // }

    public function packageBooted(): void
    {
        parent::packageBooted();

        FilamentAsset::register(
            $this->getAssets()
        );

        if($this->app->runningInConsole()) {
            $this->publishResources();
        }

        $this->registerRoutes();

        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'vb-email-templates');
    }

    protected function publishResources()
    {
        $this->publishes([
                             __DIR__
                             .'/../database/seeders/EmailTemplateSeeder.php' => database_path('seeders/EmailTemplateSeeder.php'),
                         ], 'filament-email-templates-seeds');

        $this->publishes([
                             __DIR__.'/../media/' => public_path('media/email-templates'),
                         ], 'filament-email-templates-assets');

        $this->publishes([
                             __DIR__.'/../resources/views' => resource_path('views/vendor/vb-email-templates'),
                         ], 'filament-email-templates-assets');
    }

    /**
     * @return array<Asset>
     */
    protected function getAssets(): array
    {
        return [
            Css::make('vb-email-templates-styles','https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/3.5.0/css/flag-icon.min.css'),
        ];
    }

    /**
     * Register custom routes.
     * We may want to move these to a separate file.
     * @return void
     */
    public function registerRoutes()
    {
        Route::get('/admin/email-templates/{record}/preview', [EmailTemplateController::class, 'preview'])->name('email-template.preview');
    }
}
