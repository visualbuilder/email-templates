<?php

namespace Visualbuilder\EmailTemplates\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Visualbuilder\EmailTemplates\Traits\BuildGenericEmail;

class UserVerifyEmail extends Mailable
{
    use Queueable;
    use SerializesModels;
    use BuildGenericEmail;

    public $template = 'user-verify-email';
    public $sendTo;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(public Model $user, public string $verificationUrl)
    {
        $this->sendTo = $user->email;
    }
}
