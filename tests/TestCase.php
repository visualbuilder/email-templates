<?php

namespace Visualbuilder\EmailTemplates\Tests;

use BladeUI\Heroicons\BladeHeroiconsServiceProvider;
use BladeUI\Icons\BladeIconsServiceProvider;
use Filament\Actions\ActionsServiceProvider;
use Filament\FilamentServiceProvider;
use Filament\Forms\FormsServiceProvider;
use Filament\Notifications\NotificationsServiceProvider;
use Filament\Support\SupportServiceProvider;
use Filament\Tables\TablesServiceProvider;
use Filament\Widgets\WidgetsServiceProvider;
use FilamentTiptapEditor\FilamentTiptapEditorServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use RyanChandler\BladeCaptureDirective\BladeCaptureDirectiveServiceProvider;
use Visualbuilder\EmailTemplates\EmailTemplatesServiceProvider;
use Visualbuilder\EmailTemplates\Models\EmailTemplateTheme;
use Visualbuilder\EmailTemplates\Tests\Models\User;

class TestCase extends Orchestra
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(
            User::create(['email' => 'admin@domain.com', 'name' => 'Admin', 'password' => 'password'])
        );

        Config::set('filament-email-templates.recipients', ['\\Visualbuilder\\EmailTemplates\\Tests\\Models\\User']);
    }

    protected function getPackageProviders($app): array
    {
        return [
            EmailTemplatesServiceProvider::class,
            LivewireServiceProvider::class,
            BladeCaptureDirectiveServiceProvider::class,
            FilamentServiceProvider::class,
            SupportServiceProvider::class,
            FormsServiceProvider::class,
            TablesServiceProvider::class,
            BladeHeroiconsServiceProvider::class,
            BladeIconsServiceProvider::class,
            NotificationsServiceProvider::class,
            FilamentTiptapEditorServiceProvider::class,
            AdminPanelProvider::class,
            ActionsServiceProvider::class,
            WidgetsServiceProvider::class,
        ];
    }

    protected function defineDatabaseMigrations()
    {
        $this->loadLaravelMigrations();
        $this->loadMigrationsFrom(__DIR__ . '/migrations');
    }

    protected function defineDatabaseFactories()
    {
        $this->withFactories(__DIR__ . '/factories');
    }

    public function makeTheme()
    {
        EmailTemplateTheme::factory()->create(
            [
                'name' => 'Modern Bold',
                'colours' => [
                    'header_bg_color' => '#1E88E5',
                    'body_bg_color' => '#f4f4f4',
                    'content_bg_color' => '#FFFFFB',
                    'footer_bg_color' => '#34495E',

                    'callout_bg_color' => '#FFC107',
                    'button_bg_color' => '#FFEB3B',

                    'body_color' => '#333333',
                    'callout_color' => '#212121',
                    'button_color' => '#2A2A11',
                    'anchor_color' => '#1E88E5',
                ],
                'is_default' => 1,
            ]
        );
    }
}
