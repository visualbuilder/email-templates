<?php

namespace Visualbuilder\EmailTemplates\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Visualbuilder\EmailTemplates\Contracts\TokenHelperInterface;
use Visualbuilder\EmailTemplates\Traits\BuildGenericEmail;

class UserRegisteredEmail extends Mailable
{
    use Queueable;
    use SerializesModels;
    use BuildGenericEmail;

    public $template = 'user-welcome';
    public $sendTo;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user, TokenHelperInterface $tokenHelper)
    {
        $this->user = $user;
        $this->sendTo = $user->email;
        $this->initializeTokenHelper($tokenHelper);
    }
}
