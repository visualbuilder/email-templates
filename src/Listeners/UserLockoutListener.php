<?php

namespace Visualbuilder\EmailTemplates\Listeners;

use Illuminate\Auth\Events\Login;

class UserLockoutListener
{
    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(Login $event)
    {
        $user = $event->user;
        $user->notify(new UserLoNotification());

    }
}
