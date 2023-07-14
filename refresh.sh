#!/bin/bash
#For developer to recopy from source
php artisan vendor:publish --tag=filament-email-templates-config --force
php artisan vendor:publish --tag=filament-email-templates-migrations --force
php artisan vendor:publish --tag=filament-email-templates-seeds --force
php artisan vendor:publish --tag=vb-email-templates-views --force
php artisan vendor:publish --tag=filament-email-templates-media --force
