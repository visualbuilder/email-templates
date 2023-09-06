<?php

namespace Visualbuilder\EmailTemplates\Commands;

use Database\Seeders\EmailTemplateSeeder;
use Database\Seeders\EmailTemplateThemeSeeder;
use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected bool $shouldPublishConfigFile = true;

    protected bool $shouldPublishAssets = true;

    protected bool $shouldPublishMigrations = true;

    protected bool $askToRunMigrations = true;

    protected bool $shouldPublishSeeders = true;

    protected bool $askToRunSeeders = true;

    public function __construct()
    {
        $this->signature = 'filament-email-templates:install';

        $this->description = 'Install email template editor for Filament';

        parent::__construct();
    }

    public function handle()
    {
        $this->info("Installing Email Templates");

        if ($this->shouldPublishConfigFile) {
            $this->comment('Publishing config file...');

            $this->callSilently("vendor:publish", [
                '--tag' => "filament-email-templates-config",
            ]);
        }

        if ($this->shouldPublishAssets) {
            $this->comment('Publishing assets...');

            $this->callSilently("vendor:publish", [
                '--tag' => "filament-email-templates-assets",
            ]);
        }

        if ($this->shouldPublishMigrations) {
            $this->comment('Publishing migration...');

            $this->callSilently("vendor:publish", [
                '--tag' => "filament-email-templates-migrations",
            ]);
        }

        if ($this->askToRunMigrations) {
            if ($this->confirm('Would you like to run the migrations now?')) {
                $this->comment('Running migrations...');
                $this->call('migrate');
            }
        }

        if ($this->shouldPublishSeeders) {
            $this->comment('Publishing seeders...');

            $this->callSilently("vendor:publish", [
                '--tag' => "filament-email-templates-seeds",
            ]);
        }

        if ($this->askToRunSeeders) {
            if ($this->confirm('Would you like to run the seeders now?')) {
                $this->comment('Running seeders...');
                $this->call(EmailTemplateSeeder::class);
                $this->call(EmailTemplateThemeSeeder::class);
            }
        }

        $this->info("All Done");

        return Command::SUCCESS;
    }
}
