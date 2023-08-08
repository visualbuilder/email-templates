<?php

namespace Visualbuilder\EmailTemplates\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakeEmailTemplateResource extends Command
{
    protected $signature = 'make:EmailTemplateResource {name}';
    protected $description = 'Create a Blade view template for email';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $name = $this->argument('name');
        $stub = File::get(__DIR__ .'/../Stubs/email_template.stub');
        $viewPath = resource_path('views/vendor/vb-email-templates/email/' . $name . '.blade.php');

        if (!file_exists($viewPath)) {
            File::put($viewPath, $stub);
            $this->info("Email template resource '{$name}'.blade.php created successfully.");
        } else {
            $this->error("Email template resource '{$name}'.blade.php already exists.");
        }
    }
}
