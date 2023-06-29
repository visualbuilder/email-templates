<?php

namespace Visualbuilder\EmailTemplates\Mail;

use Visualbuilder\EmailTemplates\Contracts\TokenHelperInterface;
use Visualbuilder\EmailTemplates\Traits\BuildGenericEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UserLockedOutEmail extends Mailable
{
    use Queueable, SerializesModels, BuildGenericEmail;
	
	public $template = 'user-locked-out';
	public $sendTo;
	
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user,TokenHelperInterface $tokenHelper)
    {
		$this->sendTo = $user;
        $this->initializeTokenHelper($tokenHelper);
    }
	
}
