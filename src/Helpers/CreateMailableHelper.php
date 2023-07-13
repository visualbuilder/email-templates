<?php

namespace Visualbuilder\EmailTemplates\Helpers;

use Visualbuilder\EmailTemplates\Models\EmailTemplate;
use Visualbuilder\EmailTemplates\Contracts\CreateMailableInterface;

class CreateMailableHelper implements CreateMailableInterface
{
    public function createMailable($record)
    {
        $response = (object)[
            "title"     => null,
            "icon"      => null,
            "icon_color" =>  null
        ];
        $emailTemplate = EmailTemplate::findOrFail($record->mountedTableActionRecord);

        // preparing class name
        $className = str_replace('-', ' ', $emailTemplate->key);
        $className = str_replace(' ', '', ucwords($className));

        $filePath = __DIR__ . "/../Mail/$className.php";

        if(file_exists($filePath)) {
            $response->title = "Class already exists";
            $response->icon = "heroicon-o-exclamation-circle";
            $response->icon_color = "danger";
        } else {
            $stub = file_get_contents(__DIR__ . "/../Stubs/MailableTemplate.stub");
            $classContent = str_replace(
                ['{{className}}', '{{template-key}}'],
                [$className, $emailTemplate->key],
                $stub
            );
            file_put_contents($filePath, $classContent);

            $response->title = "Class generated successfully";
            $response->icon = "heroicon-o-check-circle";
            $response->icon_color = "success";
        }

        return $response;
    }    
}
