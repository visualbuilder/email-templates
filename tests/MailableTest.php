<?php

use Illuminate\Support\Str;
use Visualbuilder\EmailTemplates\Mail\UserLockedOutEmail;
use Visualbuilder\EmailTemplates\Mail\UserLoginEmail;
use Visualbuilder\EmailTemplates\Mail\UserPasswordResetSuccessEmail;
use Visualbuilder\EmailTemplates\Mail\UserRegisteredEmail;
use Visualbuilder\EmailTemplates\Mail\UserRequestPasswordResetEmail;
use Visualbuilder\EmailTemplates\Mail\UserVerifiedEmail;
use Visualbuilder\EmailTemplates\Mail\UserVerifyEmail;
use Visualbuilder\EmailTemplates\Models\EmailTemplate;
use Visualbuilder\EmailTemplates\Tests\Models\User;

it('can replace tokens in user welcome email', function () {
    EmailTemplate::factory()->create(
        [
            'key' => 'user-welcome',
            'name' => 'User Welcome Email',
            'title' => 'Welcome to ##config.app.name##',
            'subject' => 'Welcome to ##config.app.name##',
            'preheader' => 'Lets get you started',
            'content' => "<p>Dear ##user.name##,</p>
                            <p>Thanks for registering with ##config.app.name##.</p>
                            <p>If you need any assistance please contact our customer services team ##config.email-templates.customer-services.email## who will be happy to help.</p>
                            <p>Kind Regards<br>
                            ##config.app.name##</p>",
        ]
    );

    $this->makeTheme();
    $user = User::factory()->create();
    $mailable = new UserRegisteredEmail($user);
    $mailable->assertSeeInHtml("Dear $user->name,");
});

it('can replace tokens in user password reset request email', function () {
    EmailTemplate::factory()->create(
        [
            'key' => 'user-request-reset',
            'name' => 'User Request Password Reset',
            'title' => 'Reset your password',
            'subject' => '##config.app.name## Password Reset',
            'preheader' => 'Reset Password',
            'content' => "<p>Hello ##user.name##,</p>
                            <p>You are receiving this email because we received a password reset request for your account.</p>
                            <div>##button url='##tokenURL##' title='Change My Password'##</div>
                            <p>If you didn't request this password reset, no further action is needed. However if this has happened more than once in a short space of time, please let us know.</p>
                            <p>We'll never ask for your credentials over the phone or by email and you should never share your credentials</p>
                            <p>If you’re having trouble clicking the 'Change My Password' button, copy and paste the URL below into your web browser:</p>
                            <p><a href='##tokenURL##'>##tokenURL##</a></p>
                            <p>Kind Regards,<br>##config.app.name##</p>",
        ]
    );

    $this->makeTheme();
    $user = User::factory()->create();
    $token = Password::broker()->createToken($user);
    $tokenUrl = "https://yourwebsite.com/user/password/reset/$token";
    $mailable = new UserRequestPasswordResetEmail($user, $tokenUrl);
    $mailable->assertSeeInHtml("Hello $user->name,");
    $mailable->assertSeeInHtml($tokenUrl);

});

it('can replace tokens in user password reset success email', function () {
    EmailTemplate::factory()->create(
        [
            'key' => 'user-password-reset-success',
            'name' => 'User Password Reset',
            'title' => 'Password Reset Success',
            'subject' => '##config.app.name## password has been reset',
            'preheader' => 'Success',
            'content' => "<p>Dear ##user.name##,</p>
                            <p>Your password has been reset.</p>
                            <p>Kind Regards,<br>##config.app.name##</p>",
        ]
    );

    $this->makeTheme();
    $user = User::factory()->create();
    $mailable = new UserPasswordResetSuccessEmail($user);
    $mailable->assertSeeInHtml("Dear $user->name,");
    $mailable->assertSeeInHtml("Your password has been reset.");

});

it('can replace tokens in user account locked out email', function () {
    EmailTemplate::factory()->create(
        [
            'key' => 'user-locked-out',
            'name' => 'User Account Locked Out',
            'title' => 'Account Locked',
            'subject' => '##config.app.name## account has been locked',
            'preheader' => 'Oops!',
            'content' => "<p>Dear ##user.name##,</p>
                            <p>Sorry your account has been locked out due to too many bad password attempts.</p>
                            <p>Please contact our customer services team on ##config.email-templates.customer-services.email## who will be able to help</p>
                            <p>Kind Regards,<br>##config.app.name##</p>",
        ]
    );

    $this->makeTheme();
    $user = User::factory()->create();
    $mailable = new UserLockedOutEmail($user);
    $mailable->assertSeeInHtml("Dear $user->name,");
    $mailable->assertSeeInHtml("Sorry your account has been locked out due to too many bad password attempts.");

});

it('can replace tokens in user verify email', function () {
    EmailTemplate::factory()->create(
        [
            'key' => 'user-verify-email',
            'name' => 'User Verify Email',
            'title' => 'Verify your email',
            'subject' => 'Verify your email with ##config.app.name##',
            'preheader' => 'Gain Access Now',
            'content' => "<p>Dear ##user.name##,</p>
                            <p>Your receiving this email because your email address has been registered on ##config.app.name##.</p>
                            <p>To activate your account please click the button below.</p>
                            <div>##button url='##verificationUrl##' title='Verify Email Address'##</div>
                            <p>If you’re having trouble clicking the 'Verify Email Address' button, copy and paste the URL below into your web browser:</p>
                            <p><a href='##verificationUrl##'>##verificationUrl##</a></p>
                            <p>Kind Regards,<br>##config.app.name##</p>",
        ]
    );

    $this->makeTheme();
    $user = User::factory()->create();
    $token = Str::random(64);
    $verificationUrl = "https://yourwebsite.com/verify-email/$user->id/$token";
    $mailable = new UserVerifyEmail($user, $verificationUrl);
    $mailable->assertSeeInHtml("Dear $user->name,");
    $mailable->assertSeeInHtml($verificationUrl);
});

it('can replace tokens in user verified email', function () {
    EmailTemplate::factory()->create(
        [
            'key' => 'user-verified',
            'name' => 'User Verified',
            'title' => 'Verification Success',
            'subject' => 'Verification success for ##config.app.name##',
            'preheader' => 'Verification success for ##config.app.name##',
            'content' => "<p>Hi ##user.name##,</p>
                            <p>Your email address ##user.email## has been verified on ##config.app.name##</p>
                            <p>Kind Regards,<br>##config.app.name##</p>",
        ]
    );

    $this->makeTheme();
    $user = User::factory()->create();
    $mailable = new UserVerifiedEmail($user);
    $mailable->assertSeeInHtml($user->email);

});

it('can replace tokens in user logged in email', function () {
    EmailTemplate::factory()->create(
        [
            'key' => 'user-login',
            'name' => 'User Logged In',
            'title' => 'Login Success',
            'subject' => 'Login Success for ##config.app.name##',
            'preheader' => 'Login Success for ##config.app.name##',
            'content' => "<p>Hi ##user.name##,</p>
                            <p>You have been logged into ##config.app.name##.</p>
                            <p>If this was not you please contact: </p>
                            <p>You can disable this email in your account notification preferences.</p>
                            <p>Kind Regards,<br>##config.app.name##</p>",
        ]
    );

    $this->makeTheme();
    $user = User::factory()->create();
    $mailable = new UserLoginEmail($user);
    $mailable->assertSeeInHtml("Hi $user->name,");
});
