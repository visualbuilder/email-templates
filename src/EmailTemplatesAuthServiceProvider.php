<?php

namespace Visualbuilder\EmailTemplates;


use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Visualbuilder\EmailTemplates\Mail\UserVerifyEmail;

class EmailTemplatesAuthServiceProvider extends ServiceProvider
{
    /**
     * Register any other events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        if(config('filament-email-templates.send_emails.verification')) {
            //Override default Laravel VerifyEmail notification toMail function
            VerifyEmail::toMailUsing(function ($user, string $verificationUrl) {
                return (new UserVerifyEmail($user, $verificationUrl));
            });
        }

    }
}
