<?php

namespace Visualbuilder\EmailTemplates\Listeners;

use Illuminate\Auth\Events\Verified;
use Visualbuilder\EmailTemplates\Notifications\UserVerifiedNotification;

class UserVerifiedListener
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
    public function handle(Verified $event)
    {
        if(config('filament-email-templates.send_emails.user_verified')) {
            $user = $event->user;
            $user->notify(new UserVerifiedNotification());
        }
    }
}
