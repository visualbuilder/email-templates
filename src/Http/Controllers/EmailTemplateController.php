<?php

namespace Visualbuilder\EmailTemplates\Http\Controllers;

use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Routing\Controller;
use Visualbuilder\EmailTemplates\Contracts\TokenHelperInterface;
use Visualbuilder\EmailTemplates\Models\EmailTemplate;

class EmailTemplateController extends Controller
{
    public function preview($record, TokenHelperInterface $tokenHelper)
    {
        Debugbar::disable();
        $emailTemplate = EmailTemplate::findOrFail($record);
        $model = $emailTemplate->getEmailPreviewData();

        $data = $this->prepareEmailData($emailTemplate, $tokenHelper, $model);

        return view($emailTemplate->view_path, ['data' => $data]);
    }


    protected function prepareEmailData($emailTemplate, $tokenHelper, $model)
    {
        return [
            'user'          => $model->user,
            'content'       => $tokenHelper->replaceTokens($emailTemplate->content, $model),
            'subject'       => $tokenHelper->replaceTokens($emailTemplate->subject, $model),
            'preHeaderText' => $tokenHelper->replaceTokens($emailTemplate->preheader, $model),
            'title'         => $tokenHelper->replaceTokens($emailTemplate->title, $model),
        ];
    }
}
