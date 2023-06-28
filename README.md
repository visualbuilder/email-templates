# Email template editor for Filament

[![Latest Version on Packagist](https://img.shields.io/packagist/v/visualbuilder/email-templates.svg?style=flat-square)](https://packagist.org/packages/visualbuilder/email-templates)
[![Total Downloads](https://img.shields.io/packagist/dt/visualbuilder/email-templates.svg?style=flat-square)](https://packagist.org/packages/visualbuilder/email-templates)
![GitHub Actions](https://github.com/visualbuilder/email-templates/actions/workflows/main.yml/badge.svg)

This package provides:-
 - Content management for email templates allowing authorised users to edit email template content in the admin.
 - Templates can include model attribute tokens which are replaced at run time.
 - Templates can be saved with different locales for multi-lingual capability.
 - A generic method for creating mail classes to simplify adding new templates. 

## Installation

You can install the package via composer:

```bash
composer require visualbuilder/email-templates
```

Publish the config file with:
```bash
php artisan vendor:publish --provider="Visualbuilder\EmailTemplates\EmailTemplatesServiceProvider"
```

In the newly created config file ``config/email-templates.php`` you can override default settings:-
```php
    'default-locale'=>'en_GB',
    'header-colour' => '#4a2725',
    
    //Models who can receive emails
    'recipients'    => [
        (object)[
            'id'    => 'user',
            'name'  => 'User',
            'model' => '\\App\\Models\\User'],
    ],
    
    //Guards who are authorised to edit templates
    'editor-guards'=>['web'],
```

Now publish the migrations and create the email templates table
```bash
php artisan vendor:publish --tag=email-template-filament-plugin-migrations
php artisan migrate
```

Finally, a seeder has been created with some of the typical laravel authentication email templates.
You can edit these first in the published file at ``database/seeders/EmailTemplateSeeder.php`` or just publish them and edit in the admin panel.

```bash
php artisan vendor:publish --tag=email-template-filament-plugin-seeds
php artisan db:seed --class=EmailTemplateSeeder
```

## Usage

```php
// Usage description here
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

