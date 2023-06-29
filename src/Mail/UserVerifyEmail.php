<?php

namespace Visualbuilder\EmailTemplates\Mail;

use Visualbuilder\EmailTemplates\Contracts\TokenHelperInterface;
use Visualbuilder\EmailTemplates\Traits\BuildGenericEmail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UserVerifyEmail extends Mailable
{
	use Queueable, SerializesModels, BuildGenericEmail;

	public $user;
	public $verificationUrl;
	public $template = 'user-verify-email';
	public $sendTo;
	
	/**
	 * Create a new message instance.
	 *
	 * @return void
	 */
	public function __construct($user, $token, TokenHelperInterface $tokenHelper)
	{
		$this->user = $user;
		$this->verificationUrl = $token;
		$this->sendTo = $user->email;
        $this->initializeTokenHelper($tokenHelper);
	}
	
}
