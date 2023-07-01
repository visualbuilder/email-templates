<?php

namespace Visualbuilder\EmailTemplates\Http\Controllers;

use App\Helpers\TokenHelper;
use Illuminate\Routing\Controller;
use Visualbuilder\EmailTemplates\Contracts\TokenHelperInterface;
use Visualbuilder\EmailTemplates\Models\EmailTemplate;

class EmailTemplateController extends Controller

{
    public function preview($record, TokenHelperInterface $tokenHelper) {

        $emailTemplate = EmailTemplate::findOrFail($record);

        $model = $emailTemplate->getEmailPreviewData();

        $data = [
            'user'          => $model->user,
            'content'       => $tokenHelper->replaceTokens($emailTemplate->content, $model),
            'subject'       => $tokenHelper->replaceTokens($emailTemplate->subject, $model),
            'preHeaderText' => $tokenHelper->replaceTokens($emailTemplate->preheader, $model),
            'title'         => $tokenHelper->replaceTokens($emailTemplate->title, $model)
        ];

        return view(
            'vendor/vb-email-templates/email/generic_email', ['data' => $data]
        );
    }
}
