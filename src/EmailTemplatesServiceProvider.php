<?php

namespace Visualbuilder\EmailTemplates;

use Filament\PluginServiceProvider;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Request;
use Spatie\LaravelPackageTools\Package;
use Visualbuilder\EmailTemplates\Contracts\TokenHelperInterface;
use Visualbuilder\EmailTemplates\Helpers\TokenHelper;
use Visualbuilder\EmailTemplates\Resources\EmailTemplateResource;

class EmailTemplatesServiceProvider extends PluginServiceProvider
{
    protected array $resources = [
        EmailTemplateResource::class,
    ];
    
    public function configurePackage(Package $package): void {
        $package->name("filament-email-templates")
            ->hasMigrations(['create_email_templates_table'])
            ->hasConfigFile('email-templates')
            ->hasViews('vb-email-templates')
            ->runsMigrations();
    }
    
    public function register() {
        parent::register();
        $this->app->singleton(TokenHelperInterface::class, TokenHelper::class);
        $this->app->register(EmailTemplatesEventServiceProvider::class);
        
        $this->mergeConfigFrom(
            __DIR__.'/../config/email-template-form-fields.php', 'email-template-form-fields'
        );
    }
    
    public function boot() {
        parent::boot();
        if($this->app->runningInConsole()) {
            $this->publishResources();
        }
    }
    
    protected function publishResources() {
        $this->publishes([
                             __DIR__
                             .'/../database/seeders/EmailTemplateSeeder.php' => database_path('seeders/EmailTemplateSeeder.php'),
                         ], 'filament-email-templates-seeds');
        
        $this->publishes([
                             __DIR__.'/../media/' => public_path('media/email-templates'),
                         ], 'filament-email-templates-media');
    }
}
