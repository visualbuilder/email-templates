<?php

namespace Visualbuilder\EmailTemplates\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Redirect;
use Visualbuilder\EmailTemplates\Contracts\TokenHelperInterface;
use Visualbuilder\EmailTemplates\Models\EmailTemplate;

class EmailTemplateController extends Controller
{
    public function preview($record, TokenHelperInterface $tokenHelper)
    {

        $emailTemplate = EmailTemplate::findOrFail($record);

        $model = $emailTemplate->getEmailPreviewData();

        $data = [
            'user' => $model->user,
            'content' => $tokenHelper->replaceTokens($emailTemplate->content, $model),
            'subject' => $tokenHelper->replaceTokens($emailTemplate->subject, $model),
            'preHeaderText' => $tokenHelper->replaceTokens($emailTemplate->preheader, $model),
            'title' => $tokenHelper->replaceTokens($emailTemplate->title, $model),
        ];

        return view(
            $emailTemplate->view_path,
            ['data' => $data]
        );
    }

    public function generateMailable($record)
    {
        $emailTemplate = EmailTemplate::findOrFail($record);

        // preparing class name
        $className = str_replace('-', ' ', $emailTemplate->key);
        $className = str_replace(' ', '', ucwords($className));

        $filePath = __DIR__ . "/../../Mail/$className.php";

        if(file_exists($filePath)) {
            return Redirect::back()->with('notification', 'Class already exists!');
        } else {
            $stub = file_get_contents(__DIR__ . "/../../Stubs/MailableTemplate.stub");

            $classContent = str_replace(
                ['{{className}}', '{{template-key}}'],
                [$className, $emailTemplate->key],
                $stub
            );
            file_put_contents($filePath, $classContent);

            return Redirect::back()->with('notification', 'Class generated successfully!');
        }
    }
}
