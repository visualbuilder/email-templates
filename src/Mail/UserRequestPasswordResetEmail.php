<?php

namespace Visualbuilder\EmailTemplates\Mail;

use Visualbuilder\EmailTemplates\Contracts\TokenHelperInterface;
use Visualbuilder\EmailTemplates\Traits\BuildGenericEmail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UserRequestPasswordResetEmail extends Mailable
{
	use Queueable, SerializesModels, BuildGenericEmail;

	public $user;
	public $token;
	public $template = 'user-request-reset';
	public $sendTo;
	/**
	 * Create a new message instance.
	 *
	 * @return void
	 */
	public function __construct($user, $token, TokenHelperInterface $tokenHelper)
	{
		$this->user = $user;
		$this->tokenUrl = $token;
		$this->sendTo = $user->email;
        $this->initializeTokenHelper($tokenHelper);
	}

}
