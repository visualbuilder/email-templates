<?php

namespace Visualbuilder\EmailTemplates\Listeners;

use Illuminate\Auth\Events\PasswordReset;

use Visualbuilder\EmailTemplates\Notifications\UserPasswordResetNotification;

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
        if(config('filament-email-templates.send_emails.password_reset_success')) {
            $user = $event->user;
            $user->notify(new UserPasswordResetNotification());
        }


    }
}
