<?php

use Illuminate\Support\Facades\Notification;
use Visualbuilder\EmailTemplates\Notifications\UserLockoutNotification;
use Visualbuilder\EmailTemplates\Tests\Models\User;

it('does not send locked out notification when disabled', function () {
    Notification::fake();

    config()->set('filament-email-templates.send_emails.locked_out', false);

    $user = User::create([
        'name' => 'Test User',
        'email' => 'user@example.com',
        'password' => 'password',
    ]);

    $user->notify(new UserLockoutNotification());

    Notification::assertNothingSent();
});

