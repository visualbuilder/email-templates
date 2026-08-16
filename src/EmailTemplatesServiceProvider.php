<?php

namespace Visualbuilder\EmailTemplates;

use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Visualbuilder\EmailTemplates\Commands\InstallCommand;
use Visualbuilder\EmailTemplates\Commands\WrapContentTokensCommand;
use Visualbuilder\EmailTemplates\Contracts\CreateMailableInterface;
use Visualbuilder\EmailTemplates\Contracts\FormHelperInterface;

use Visualbuilder\EmailTemplates\Helpers\CreateMailableHelper;
use Visualbuilder\EmailTemplates\Helpers\FormHelper;

class EmailTemplatesServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $migrations = ['create_email_templates_themes_table', 'create_email_templates_table'];

        if (config('filament-email-templates.multitenancy.enabled')) {
            $migrations[] = 'add_tenant_to_email_templates_tables';
        }

        $package->name("filament-email-templates")
            ->hasMigrations($migrations)
            ->hasConfigFile(['filament-email-templates'])
            ->hasAssets()
            ->hasTranslations()
            ->hasViews('vb-email-templates')
            ->runsMigrations()
            ->hasCommands([
                InstallCommand::class,
                WrapContentTokensCommand::class,
            ]);
    }

    public function packageRegistered(): void
    {
        parent::packageRegistered();

        $this->loadJsonTranslationsFrom(__DIR__.'/../resources/lang/');


        $this->app->singleton(CreateMailableInterface::class, CreateMailableHelper::class);
        $this->app->singleton(FormHelperInterface::class, FormHelper::class);
        $this->app->singleton(TokenRegistry::class);
        $this->app->register(EmailTemplatesEventServiceProvider::class);

        // Add the binding for TokenReplacementInterface
        $this->app->bind(
            \Visualbuilder\EmailTemplates\Contracts\TokenReplacementInterface::class,
            config('filament-email-templates.tokenHelperClass')
        );
    }

    public function packageBooted(): void
    {
        parent::packageBooted();

        FilamentAsset::register(
            $this->getAssets()
        );

        if($this->app->runningInConsole()) {
            $this->publishResources();
        }

        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'vb-email-templates');

        $this->registerEditorProfile();
    }

    /**
     * TinyEditor profile for template editing: the default profile plus the
     * vbtokens plugin (Insert Token menu, ## autocompleter, badge display).
     * Define your own 'email-template' profile in filament-tinyeditor
     * config to take full control - the package then leaves it untouched.
     */
    protected function registerEditorProfile(): void
    {
        if (config('filament-tinyeditor.profiles.email-template')) {
            return;
        }

        $base = config('filament-tinyeditor.profiles.default', []);

        $defaultPlugins = 'accordion autoresize codesample directionality advlist autolink link image lists charmap preview anchor pagebreak searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media table emoticons help';
        $defaultToolbar = 'undo redo removeformat | styles | bold italic | rtl ltr | alignjustify alignright aligncenter alignleft | numlist bullist outdent indent accordion | forecolor backcolor | blockquote table toc hr | image link anchor media codesample emoticons | visualblocks print preview wordcount fullscreen help';

        config()->set('filament-tinyeditor.profiles.email-template', array_merge($base, [
            'plugins' => trim(($base['plugins'] ?? $defaultPlugins).' vbtokens'),
            'toolbar' => 'vbtokens | '.($base['toolbar'] ?? $defaultToolbar),
            'external_plugins' => array_merge($base['external_plugins'] ?? [], [
                'vbtokens' => asset('vendor/filament-email-templates/tiny-plugins/vbtokens.js'),
            ]),
        ]));
    }

    protected function publishResources()
    {
        $this->publishes([
                            __DIR__
                            .'/../database/seeders/EmailTemplateSeeder.php' => database_path('seeders/EmailTemplateSeeder.php'),
                            __DIR__.'/../database/seeders/EmailTemplateThemeSeeder.php' => database_path('seeders/EmailTemplateThemeSeeder.php'),
                        ], 'filament-email-templates-seeds');

        $this->publishes([
                            __DIR__.'/../media/' => public_path('media/email-templates'),
                            __DIR__.'/../resources/views' => resource_path('views/vendor/vb-email-templates'),
                        ], 'filament-email-templates-assets');
    }

    /**
     * @return array<Asset>
     */
    protected function getAssets(): array
    {
        // Flag-icon stylesheet — configurable so self-hosting / strict-CSP
        // consumers can override the URL or disable it (null/false) and load
        // their own. See config('filament-email-templates.flag_icon_stylesheet').
        $stylesheet = config(
            'filament-email-templates.flag_icon_stylesheet',
            'https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/3.5.0/css/flag-icon.min.css'
        );

        return $stylesheet
            ? [Css::make('vb-email-templates-styles', $stylesheet)]
            : [];
    }
}
