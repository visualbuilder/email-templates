# Email template editor for Filament

[![Latest Version on Packagist](https://img.shields.io/packagist/v/visualbuilder/email-templates.svg?style=flat-square)](https://packagist.org/packages/visualbuilder/email-templates)
[![Total Downloads](https://img.shields.io/packagist/dt/visualbuilder/email-templates.svg?style=flat-square)](https://packagist.org/packages/visualbuilder/email-templates)
![GitHub Actions](https://github.com/visualbuilder/email-templates/actions/workflows/main.yml/badge.svg)

This package provides content management capability for email templates allowing users to edit content, include model attributes that can be replaced at run time.

## Installation

You can install the package via composer:

```bash
composer require visualbuilder/email-templates
```

Publish the migrations to create the email templates table and migrate
```bash
php artisan vendor:publish --tag=email-template-filament-plugin-migrations
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --provider="Visualbuilder\EmailTemplates\EmailTemplatesServiceProvider"
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

