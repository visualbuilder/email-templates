<?php

namespace Visualbuilder\EmailTemplates\Listeners;

use Illuminate\Auth\Events\Verified;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
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
        $user = $event->user;
	    $user->notify(new UserVerifiedNotification());

	
    }
}
