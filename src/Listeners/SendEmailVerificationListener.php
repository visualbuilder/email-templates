<?php

namespace Visualbuilder\EmailTemplates\Listeners;

use Exception;
use Filament\Facades\Filament;
use Visualbuilder\EmailTemplates\Notifications\UserVerifyEmailNotification;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification as BaseListener;
use Illuminate\Contracts\Auth\MustVerifyEmail;

class SendEmailVerificationListener extends BaseListener
{
    public function handle(Registered $event): void
    {
        if (! $event->user instanceof MustVerifyEmail) {
            return;
        }

        if ($event->user->hasVerifiedEmail()) {
            return;
        }

        if (! method_exists($event->user, 'notify')) {
            $userClass = $event->user::class;

            throw new Exception("Model [{$userClass}] does not have a [notify()] method.");
        }

        $notification = new UserVerifyEmailNotification(Filament::getVerifyEmailUrl($event->user));

        $event->user->notify($notification);
    }
}
