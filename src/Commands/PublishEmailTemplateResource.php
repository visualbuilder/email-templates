<?php

namespace Visualbuilder\EmailTemplates\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class PublishEmailTemplateResource extends Command
{
    protected $signature = 'email-template:publish';
    protected $description = 'Publish EmailTemplateResource to your project';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // preparing directory
        $this->prepareDirectory('EmailTemplateResource');
        $this->prepareDirectory('EmailTemplateResource/Pages');

        // Publishing EmailTemplateResource.php
        $this->publishResource('EmailTemplateResource');

        // Publishing CreateEmailTemplate.php
        $this->publishResource('CreateEmailTemplate', 'EmailTemplateResource/Pages/');

        // Publishing EditEmailTemplate.php
        $this->publishResource('EditEmailTemplate', 'EmailTemplateResource/Pages/');

        // Publishing ListEmailTemplates.php
        $this->publishResource('ListEmailTemplates', 'EmailTemplateResource/Pages/');

        // Publishing PreviewEmailTemplate.php
        $this->publishResource('PreviewEmailTemplate', 'EmailTemplateResource/Pages/');

        return Command::SUCCESS;
    }

    public function prepareDirectory($folder)
    {
        $path = app_path("Filament/Resources/$folder");
        if(! File::isDirectory($path)) {
            File::makeDirectory($path, 0755, true);
        }

        return true;
    }

    public function publishResource($name, $path = "")
    {
        $stub = File::get(__DIR__ ."/../Stubs/$name.stub");
        $resourcePath = app_path("Filament/Resources/".$path.$name.".php");
        if (! file_exists($resourcePath)) {
            File::put($resourcePath, $stub);
            $this->info("$name.php created successfully.");
        } else {
            $this->error("$name.php already exists.");
        }
    }
}
