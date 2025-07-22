<?php

use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Notification;
use Visualbuilder\EmailTemplates\Tests\Models\User;
use Visualbuilder\EmailTemplates\Notifications\UserLoginNotification;
use Visualbuilder\EmailTemplates\Notifications\UserRegisteredNotification;
use Visualbuilder\EmailTemplates\Notifications\UserPasswordResetNotification;
use Visualbuilder\EmailTemplates\Notifications\UserLockoutNotification;
use Visualbuilder\EmailTemplates\Notifications\UserVerifiedNotification;
use Visualbuilder\EmailTemplates\Listeners\UserLockoutListener;

it('sends login notification based on config flag', function () {
    $user = User::factory()->create();

    // when enabled
    Notification::fake();
    config(['filament-email-templates.send_emails.login' => true]);
    event(new Login('web', $user, false));
    Notification::assertSentTo($user, UserLoginNotification::class);

    // when disabled
    Notification::fake();
    config(['filament-email-templates.send_emails.login' => false]);
    event(new Login('web', $user, false));
    Notification::assertNothingSent();
});

it('sends registered notification based on config flag', function () {
    $user = User::factory()->create();

    Notification::fake();
    config(['filament-email-templates.send_emails.new_user_registered' => true]);
    event(new Registered($user));
    Notification::assertSentTo($user, UserRegisteredNotification::class);

    Notification::fake();
    config(['filament-email-templates.send_emails.new_user_registered' => false]);
    event(new Registered($user));
    Notification::assertNothingSent();
});

it('sends password reset notification based on config flag', function () {
    $user = User::factory()->create();

    Notification::fake();
    config(['filament-email-templates.send_emails.password_reset_success' => true]);
    event(new PasswordReset($user));
    Notification::assertSentTo($user, UserPasswordResetNotification::class);

    Notification::fake();
    config(['filament-email-templates.send_emails.password_reset_success' => false]);
    event(new PasswordReset($user));
    Notification::assertNothingSent();
});

it('sends lockout notification based on config flag', function () {
    $user = User::factory()->create();

    Notification::fake();
    \Illuminate\Support\Facades\Event::listen(Login::class, UserLockoutListener::class);
    config(['filament-email-templates.send_emails.locked_out' => true]);
    event(new Login('web', $user, false));
    Notification::assertSentTo($user, UserLockoutNotification::class);

    Notification::fake();
    config(['filament-email-templates.send_emails.locked_out' => false]);
    event(new Login('web', $user, false));
    Notification::assertNotSentTo($user, UserLockoutNotification::class);
});

it('sends verified notification based on config flag', function () {
    $user = User::factory()->create();

    Notification::fake();
    config(['filament-email-templates.send_emails.user_verified' => true]);
    event(new Verified($user));
    Notification::assertSentTo($user, UserVerifiedNotification::class);

    Notification::fake();
    config(['filament-email-templates.send_emails.user_verified' => false]);
    event(new Verified($user));
    Notification::assertNothingSent();
});
