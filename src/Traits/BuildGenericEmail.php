<?php

namespace Visualbuilder\EmailTemplates\Traits;


use Visualbuilder\EmailTemplates\Contracts\TokenHelperInterface;
use Visualbuilder\EmailTemplates\Models\EmailTemplate;
use Illuminate\Support\Facades\App;

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

		if($this->attachment??false){
			$this->attach(
				$this->attachment->filepath, [
				'as'   => $this->attachment->filename,
				'mime' => $this->attachment->filetype
			]);
		}

		return $this->from($template->from,config('app.name'))
		            ->view('email.generic_email')
		            ->subject($this->tokenHelper->replaceTokens($template->subject, $this))
		            ->to($this->sendTo)
		            ->with(['content'       => $this->tokenHelper->replaceTokens($template->content, $this),
		                    'preHeaderText' => $this->tokenHelper->replaceTokens($template->preheader, $this),
		                    'title'         => $this->tokenHelper->replaceTokens($template->title, $this)
		                   ]);
	}
}
