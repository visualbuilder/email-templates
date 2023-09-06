<?php

namespace Visualbuilder\EmailTemplates\Helpers;

use Exception;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Visualbuilder\EmailTemplates\Contracts\CreateMailableInterface;

class CreateMailableHelper implements CreateMailableInterface
{
    public const STUB_PATH = __DIR__."/../Stubs/MailableTemplate.stub";

    public function createMailable($record)
    {
        try {
            $className = Str::studly($record->key);

            $this->prepareDirectory(config('email-templates.mailable_directory'));

            $filePath = app_path(config('email-templates.mailable_directory')."/$className.php");

            if (file_exists($filePath)) {
                return $this->response("Class already exists", "heroicon-o-exclamation-circle", "danger", $filePath);
            }

            $classContent = str_replace(['{{className}}', '{{template-key}}'], [$className, $record->key], File::get(self::STUB_PATH));

            File::put($filePath, $classContent);

            return $this->response("Class generated successfully", "heroicon-o-check-circle", "success", $filePath);

        } catch (Exception $e) {
            Log::error($e->getMessage());

            return $this->response("Error: ".$e->getMessage(), "heroicon-o-exclamation-circle", "danger");
        }
    }

    private function prepareDirectory($folder)
    {
        $path = app_path($folder);
        File::ensureDirectoryExists($path, 0755);
    }

    private function response($title, $icon, $icon_color, $body)
    {
        return (object) [
            "title"      => $title,
            "icon"       => $icon,
            "icon_color" => $icon_color,
            "body"       => $body,
        ];
    }
}
