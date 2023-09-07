<?php

namespace Visualbuilder\EmailTemplates\Traits;

use Illuminate\Support\Facades\App;
use Visualbuilder\EmailTemplates\Contracts\TokenHelperInterface;
use Visualbuilder\EmailTemplates\Models\EmailTemplate;

trait BuildGenericEmail
{
    private $tokenHelper;

    public function initializeTokenHelper(TokenHelperInterface $tokenHelper)
    {
        $this->tokenHelper = $tokenHelper;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $template = EmailTemplate::findEmailByKey($this->template, App::currentLocale());

        if($this->attachment ?? false) {
            $this->attach(
                $this->attachment->filepath,
                [
                'as' => $this->attachment->filename,
                'mime' => $this->attachment->filetype,
            ]
            );
        }

        $data = [
            'content' => $this->tokenHelper->replaceTokens($template->content, $this),
            'preHeaderText' => $this->tokenHelper->replaceTokens($template->preheader, $this),
            'title' => $this->tokenHelper->replaceTokens($template->title, $this),
            'theme' => $template->theme->colours,
        ];

        return $this->from($template->from, config('app.name'))
            ->view($template->view_path)
            ->subject($this->tokenHelper->replaceTokens($template->subject, $this))
            ->to($this->sendTo)
            ->with(['data' => $data]);
    }
}
