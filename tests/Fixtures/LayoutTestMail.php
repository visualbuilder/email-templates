<?php

namespace Visualbuilder\EmailTemplates\Tests\Fixtures;

use Illuminate\Mail\Mailable;
use Visualbuilder\EmailTemplates\Traits\BuildGenericEmail;

/** Mailable through the package trait, without an unsubscribe URL. */
class LayoutTestMail extends Mailable
{
    use BuildGenericEmail;

    public $template = 'layout-test';

    public $sendTo;

    public function __construct(public $user)
    {
        $this->sendTo = $user->email;
    }
}
