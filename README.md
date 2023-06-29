# Email template editor for Filament

[![Latest Version on Packagist](https://img.shields.io/packagist/v/visualbuilder/email-templates.svg?style=flat-square)](https://packagist.org/packages/visualbuilder/email-templates)
[![Total Downloads](https://img.shields.io/packagist/dt/visualbuilder/email-templates.svg?style=flat-square)](https://packagist.org/packages/visualbuilder/email-templates)
![GitHub Actions](https://github.com/visualbuilder/email-templates/actions/workflows/main.yml/badge.svg)

### Why businesses and applications should use Email Templates
- **Time-saving**: Email templates eliminate the need to create emails from scratch, saving valuable time and effort.
- **Customizability**: Quick editing capabilities enable employees to personalize the content of the templates while maintaining a professional appearance.
- **Consistent branding**: Templates ensure that all emails adhere to the brand's guidelines, reinforcing brand recognition and professionalism.
- **Professional appearance**: Well-designed templates provide a polished and consistent look, enhancing the business's credibility and reputation.
- **Streamlined communication**: Prompt and efficient communication.
- **Flexibility**: Templates can be adapted for various purposes, such as promotional emails, customer support responses, newsletters, and more.
- **Easy updates**: Templates can be easily modified to reflect changes in offers, policies, or design elements, ensuring that communication remains current and aligned with business objectives.
- **Standardization**: Templates enforce a standardized structure and format for emails, reducing errors and improving clarity in communication.
- **Scalability**: Email templates facilitate consistent messaging even as the business grows, ensuring a cohesive customer experience across all interactions.
- **Improved productivity**: With quick access to templates, employees can focus more on core tasks, increasing overall productivity within the business.

### This package provides:-
- Content management for email templates allowing authorised users to edit email template content in the admin.
- Templates can include model attribute tokens or config values which will be replaced, eg ##user.firstname## or ##config.app.name##
- Templates can be saved with different locales for multi-lingual capability.
- A generic method for quickly creating mail classes to speed up adding new templates and faster automation possiblities.

We use the standard Laravel mail sending capability, the package simply allows content editing and faster adding of new templates.

## Installation

You can install the package via composer:

```bash
composer require visualbuilder/email-templates
```

1. Publish the config file with:
```bash
 php artisan vendor:publish --tag=filament-email-templates-config
```

2. In the newly created config file ``config/email-templates.php`` you can override default settings:-
```php
    'default-locale'=>'en_GB',
    'header-colour' => '#4a2725',
    
    //Models who can receive email
    'recipients'    => [
        (object)[
            'id'    => 'user',
            'name'  => 'User',
            'model' => '\\App\\Models\\User'],
    ],
    
    //Guards who are authorised to edit templates
    'editor-guards'=>['web'],
```

3.  Publish migrations and create the email templates table
```bash
php artisan vendor:publish --tag=filament-email-templates-migrations
php artisan migrate
```

4. Publish the default email contents.
A seeder has been created containing email template content for typical laravel authentication actions.
After publishing the file, you can edit the seeder at ``database/seeders/EmailTemplateSeeder.php`` 

or just seed them and edit in the admin panel using the HTML editor.

```bash
php artisan vendor:publish --tag=filament-email-templates-seeds
php artisan db:seed --class=EmailTemplateSeeder
```

5. Publish the HTML Email Template view files.
```bash
php artisan vendor:publish --tag=filament-email-templates-views
php artisan db:seed --class=EmailTemplateSeeder
```
The HTML email template has been tested to work on multiple devices, mail clients and screen sizes.  
You can of course edit the header, logo, footer etc or replace it with your own email format completely.


## Usage

### Implementing out of the box templates

Emails may be sent directly, via a notification or from an event listener.  The following email templates are included to get you started and show different methods of sending.

 - **User Registered**  - Welcome them to the platform
 - **User Verify Email** - Check they are human
 - **User Request Password Reset** - Let them change the password
 - **User Password Reset Success** - Yay, you changed your password
 - **User Locked Out** - Oops - What to do now?
 - **User Login** - Success

Not all systems will require a login notification,  but it's good practice for security so include here.

#### New User Registered Email
A new **Registered** event is triggered when creating a new user, by default if email verification is required a send verification email event is triggered.

Its polite and good practice to welcome your user so we think this deserves a different email.

To hook into this event create a new Listener and Notification class.

```php
<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Notifications\UserRegisteredNotification;

class NewUserRegisteredListener
{
    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle( Registered $event)
    {
        return $event->user->notify(new UserRegisteredNotification());
    }   
}
```

```php
<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class UserRegisteredNotification extends Notification implements ShouldQueue
{
    use Queueable, HasUserPreferences;

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
         return (new \Visualbuilder\EmailTemplates\Mail\UserRegisteredEmail($notifiable  ));
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [];
    }

}

```

### Testing

```bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email support@ekouk.com instead of using the issue tracker.

## Credits

-   [Visual Builder](https://github.com/visualbuilder)
-   [All Contributors](../../contributors)

## License

The GNU GPLv3. Please see [License File](LICENSE.md) for more information.

