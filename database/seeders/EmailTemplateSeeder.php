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
                'from'      => config('mail.from.address'),
                'name'      => 'User Welcome Email',
                'title'     => 'Welcome to '.config('app.name'),
                'send_to'   => 'user',
                'subject'   => 'Welcome to '.config('app.name'),
                'preheader' => 'Lets get you started',
                'content'   => "<p>Dear ##user.name##,<br>Thanks for registering with ".config('app.name').".</p>"
            ],
            [
                'key'       => 'user-request-reset',
                'from'      => config('mail.from.address'),
                'send_to'   => 'user',
                'name'      => 'User Request Password Reset',
                'title'     => 'Reset your password',
                'subject'   => config('app.name').' Password Reset',
                'preheader' => 'Reset Password',
                'content'   => "<div>Hello&nbsp;##user.name##,</div><div><br></div><div>You are receiving this email because we received a password reset request for your account.</div><div><br></div><div>##button url='##tokenURL##' title='Change My Password'##</div><div><br></div><div>If you didn't request this password reset/update, no further action is needed. However if this has happened more than once in a short space of time, please let us know.</div><div><br></div><div>We'll never ask for your credentials either over the phone or by email and you should never share your credentials</div><div><div><br></div><div>If you’re having trouble clicking the 'Change My Password' button, copy and paste the URL below into your web browser:</div><div><br></div><p ><a href='##tokenURL##'>##tokenURL##</a></p><p>Regards,<br>".config('app.name')."</p></div>"
            ],
            [
                'key'       => 'user-password-reset-success',
                'from'      => config('mail.from.address'),
                'send_to'   => 'user',
                'name'      => 'User Password Reset',
                'title'     => 'Password Reset Success',
                'subject'   => config('app.name').' password has been reset',
                'preheader' => 'Success',
                'content'   => "<p>Dear ##user.name##,<br> Your password has been reset</p> "
            ],
            [
                'key'       => 'user-locked-out',
                'from'      => config('mail.from.address'),
                'send_to'   => 'user',
                'name'      => 'User Account Locked Out',
                'title'     => 'Account Locked',
                'subject'   => config('app.name').' account has been locked',
                'preheader' => 'Oops!',
                'content'   => "<p>Dear ##user.name##,<br> Sorry your account has been locked out due to too many bad password attempts.</p> "
            ],
            [
                'key'       => 'user-verify-email',
                'from'      => config('mail.from.address'),
                'send_to'   => 'user',
                'name'      => 'User Verify Email',
                'title'     => 'Verify your email',
                'subject'   => 'Verify your email with '.config('app.name'),
                'preheader' => 'Gain Access Now',
                'content'   => "<p>Dear ##user.name##,<br> Please click the button below to verify your email address.</p>
<div>##button url='##verificationUrl##' title='Verify Email Address'##</div>"
            ],
            [
                'key'       => 'user-verified',
                'from'      => config('mail.from.address'),
                'name'      => 'User Verified',
                'title'     => 'Verification Success',
                'send_to'   => 'user',
                'subject'   => 'Verification success for '.config('app.name'),
                'preheader' => 'Verification success for'.config('app.name'),
                'content'   => "<p>Hi ##user.name##,</p>
                                <p>Your email address ##user.email## has been verified on ##config.app.name##</p>
                                <p>Kind Regards,<br>
                                ##config.app.name##</p>"
            ],
            [
                'key'       => 'user-login',
                'from'      => config('mail.from.address'),
                'name'      => 'User Logged In',
                'title'     => 'Login Success',
                'send_to'   => 'user',
                'subject'   => 'Login Success for '.config('app.name'),
                'preheader' => 'Login Success for '.config('app.name'),
                'content'   => "<p>Hi ##user.name##,</p>
                                <p>You have been logged into ##config.app.name##.</p>
                                <p>If this was not you please contact: </p>
                                <p>You can disable this email in your account notification preferences.</p>
                                <p>Kind Regards,<br>
                                ##config.app.name##</p>"
            ],
        ];

        EmailTemplate::factory()
            ->createMany($emailTemplates);
    }
}
