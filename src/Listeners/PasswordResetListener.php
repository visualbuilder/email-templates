<?php

namespace Visualbuilder\EmailTemplates\Listeners;

use Illuminate\Auth\Events\PasswordReset;

use Visualbuilder\EmailTemplates\Notifications\UserPasswordResetNotification;
use Visualbuilder\EmailTemplates\Notifications\UserVerifiedNotification;

class PasswordResetListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(PasswordReset $event)
    {
        $user = $event->user;
        $user->notify(new UserPasswordResetNotification());


    }
}
