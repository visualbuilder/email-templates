#!/bin/bash
#For developer to remove and reinstall the package overwriting the previous views and config.

composer remove visualbuilder/email-templates
composer require visualbuilder/email-templates:dev-main

php artisan vendor:publish --tag=filament-email-templates-config --force
php artisan vendor:publish --tag=filament-email-templates-migrations --force
php artisan vendor:publish --tag=filament-email-templates-seeds --force
php artisan vendor:publish --tag=vb-email-templates-views --force

php artisan migrate:fresh --seed
php artisan db:seed --class=EmailTemplateSeeder

php artisan make:filament-user
