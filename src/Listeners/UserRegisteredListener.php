<?php

namespace Visualbuilder\EmailTemplates\Listeners;

use Illuminate\Auth\Events\Registered;
use Visualbuilder\EmailTemplates\Notifications\UserLoginNotification;

class UserRegisteredListener
{

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(Registered $event)
    {
        if(config('email-templates.send_emails.new_user_registered')){
            $user = $event->user;
            $user->notify(new UserLoginNotification());
        }


    }
}
