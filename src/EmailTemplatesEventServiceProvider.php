<?php

namespace Visualbuilder\EmailTemplates;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Visualbuilder\EmailTemplates\Listeners\PasswordResetListener;
use Visualbuilder\EmailTemplates\Listeners\UserLockoutListener;
use Visualbuilder\EmailTemplates\Listeners\UserLoginListener;
use Visualbuilder\EmailTemplates\Listeners\UserRegisteredListener;
use Visualbuilder\EmailTemplates\Listeners\UserVerifiedListener;

class EmailTemplatesEventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the package.
     *
     * @var array
     */
    protected $listen = [
        Login::class => [
            UserLoginListener::class,
        ],
        Registered::class => [
            UserRegisteredListener::class,
        ],
        PasswordReset::class => [
            PasswordResetListener::class,
        ],
        Lockout::class => [
            UserLockoutListener::class,
        ],
        Verified::class => [
            UserVerifiedListener::class,
        ],
    ];

    /**
     * Register any other events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
