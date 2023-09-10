<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Visualbuilder\EmailTemplates\Models\EmailTemplate;

class EmailTemplateSeeder extends Seeder
{
    public function run() {
        $emailTemplates = [
            [
                'key'       => 'user-welcome',
                'from'      =>  ['email'=>config('mail.from.address'),'name'=>config('mail.from.name')],
                'name'      => 'User Welcome Email',
                'title'     => 'Welcome to ##config.app.name##',
                'subject'   => 'Welcome to ##config.app.name##',
                'preheader' => 'Lets get you started',
                'content'   => "<p>Dear ##user.name##,</p>
                                <p>Thanks for registering with ##config.app.name##.</p>
                                <p>If you need any assistance please contact our customer services team ##config.email-templates.customer-services.email## who will be happy to help.</p>
                                <p>Kind Regards<br>
                                ##config.app.name##</p>"
            ],
            [
                'key'       => 'user-request-reset',
                'from'      => ['email'=>config('mail.from.address'),'name'=>config('mail.from.name')],
                'name'      => 'User Request Password Reset',
                'title'     => 'Reset your password',
                'subject'   => '##config.app.name## Password Reset',
                'preheader' => 'Reset Password',
                'content'   => "<p>Hello ##user.name##,</p>
                                <p>You are receiving this email because we received a password reset request for your account.</p>
                                <div>##button url='##tokenURL##' title='Change My Password'##</div>
                                <p>If you didn't request this password reset, no further action is needed. However if this has happened more than once in a short space of time, please let us know.</p>
                                <p>We'll never ask for your credentials over the phone or by email and you should never share your credentials</p>
                                <p>If you’re having trouble clicking the 'Change My Password' button, copy and paste the URL below into your web browser:</p>
                                <p><a href='##tokenURL##'>##tokenURL##</a></p>
                                <p>Kind Regards,<br>##config.app.name##</p>"
            ],
            [
                'key'       => 'user-password-reset-success',
                'from'      => ['email'=>config('mail.from.address'),'name'=>config('mail.from.name')],
                'name'      => 'User Password Reset',
                'title'     => 'Password Reset Success',
                'subject'   => '##config.app.name## password has been reset',
                'preheader' => 'Success',
                'content'   => "<p>Dear ##user.name##,</p>
                                <p>Your password has been reset.</p>
                                <p>Kind Regards,<br>##config.app.name##</p>"
            ],
            [
                'key'       => 'user-locked-out',
                'from'      => ['email'=>config('mail.from.address'),'name'=>config('mail.from.name')],

                'name'      => 'User Account Locked Out',
                'title'     => 'Account Locked',
                'subject'   => '##config.app.name## account has been locked',
                'preheader' => 'Oops!',
                'content'   => "<p>Dear ##user.name##,</p>
                                <p>Sorry your account has been locked out due to too many bad password attempts.</p>
                                <p>Please contact our customer services team on ##config.email-templates.customer-services.email## who will be able to help</p>
                                 <p>Kind Regards,<br>##config.app.name##</p>"

            ],
            [
                'key'       => 'user-verify-email',
                'from'      => ['email'=>config('mail.from.address'),'name'=>config('mail.from.name')],

                'name'      => 'User Verify Email',
                'title'     => 'Verify your email',
                'subject'   => 'Verify your email with ##config.app.name##',
                'preheader' => 'Gain Access Now',
                'content'   => "<p>Dear ##user.name##,</p>
                                <p>Your receiving this email because your email address has been registered on ##config.app.name##.</p>
                                <p>To activate your account please click the button below.</p>
                                <div>##button url='##verificationUrl##' title='Verify Email Address'##</div>
                                <p>If you’re having trouble clicking the 'Verify Email Address' button, copy and paste the URL below into your web browser:</p>
                                <p><a href='##verificationUrl##'>##verificationUrl##</a></p>
                                <p>Kind Regards,<br>##config.app.name##</p>"
            ],
            [
                'key'       => 'user-verified',
                'from'      => ['email'=>config('mail.from.address'),'name'=>config('mail.from.name')],
                'name'      => 'User Verified',
                'title'     => 'Verification Success',
                'subject'   => 'Verification success for ##config.app.name##',
                'preheader' => 'Verification success for ##config.app.name##',
                'content'   => "<p>Hi ##user.name##,</p>
                                <p>Your email address ##user.email## has been verified on ##config.app.name##</p>
                                <p>Kind Regards,<br>##config.app.name##</p>"
            ],
            [
                'key'       => 'user-login',
                'from'      => ['email'=>config('mail.from.address'),'name'=>config('mail.from.name')],
                'name'      => 'User Logged In',
                'title'     => 'Login Success',
                'subject'   => 'Login Success for ##config.app.name##',
                'preheader' => 'Login Success for ##config.app.name##',
                'content'   => "<p>Hi ##user.name##,</p>
                                <p>You have been logged into ##config.app.name##.</p>
                                <p>If this was not you please contact: </p>
                                <p>You can disable this email in your account notification preferences.</p>
                                <p>Kind Regards,<br>##config.app.name##</p>"
            ],
        ];

        EmailTemplate::factory()
            ->createMany($emailTemplates);
    }
}
