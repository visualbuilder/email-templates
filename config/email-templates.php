<?php

return [
    //If you wish to customise the table name change this before migration
    'table_name'       => 'vb_email_templates',

    //If you want to use your own resource for email templates, 
    //you can set this to true and use `php artisan email-template:publish` to publish the resource
    "publish_resource" => false,

    //Email templates will be copied to resources/views/vendor/vb-email-templates/email
    //default.blade.php is base view that can be customised below
    'default_view'     => 'default',

    'template_view_path'   => 'vb-email-templates::email',

    //Default Email Styling
    'logo'             => 'media/email-templates/logo.png',

    //Logo size in pixels -> 200 pixels high is plenty big enough.
    'logo_width'       => '476',
    'logo_height'      => '117',

    //Content Width in Pixels
    'content_width'    => '600',

    //Background Colours
    'header_bg_color'  => '#B8B8D1',
    'body_bg_color'    => '#f4f4f4',
    'content_bg_color' => '#FFFFFB',
    'footer_bg_color'  => '#5B5F97',
    'callout_bg_color' => '#B8B8D1',
    'button_bg_color'  => '#FFC145',

    //Text Colours
    'body_color'       => '#333333',
    'callout_color'    => '#000000',
    'button_color'     => '#2A2A11',
    'anchor_color'     => '#4c05a1',

    //Contact details included in default email templates
    'customer-services-email'=>'support@yourcompany.com',
    'customer-services-phone'=>'+441273 455702',

    //Footer Links
    'links' =>[
        ['name'=>'Website','url'=>'https://yourwebsite.com','title'=>'Goto website'],
        ['name'=>'Privacy Policy','url'=>'https://yourwebsite.com/privacy-policy','title'=>'View Privacy Policy'],
    ],

    //Options for alternative languages
    //Note that Laravel default locale is just 'en'
    //We are being more specific to cater for English vs USA languages
    'default_locale'   => 'en_GB',

    //These will be included in the language picker when editing an email template
    'languages'        => [
        'en_GB' => ['display' => 'British', 'flag-icon' => 'gb'],
        'en_US' => ['display' => 'USA', 'flag-icon' => 'us'],
        'es'    => ['display' => 'Español', 'flag-icon' => 'es'],
        'fr'    => ['display' => 'Français', 'flag-icon' => 'fr'],
        'pt'    => ['display' => 'Brasileiro', 'flag-icon' => 'br'],
        'in'    => ['display' => 'Hindi', 'flag-icon' => 'in'],
    ],

    //Notifiable Models who can receive emails
    'recipients'       => [
        '\\App\\Models\\User',
    ],

    //Guards who are authorised to edit email templates
    'editor_guards'    => ['web'],

    /**
     * Allowed config keys which can be inserted into email templates
     * eg use ##config.app.name## in the email template for automatic replacement.
     */
    'config_keys'      => [
        'app.name',
        'app.url',
        'email-templates.customer-services'
        // Add other safe config keys here.
        // We don't want to allow all config keys they may contain secret keys or credentials
    ],

    //Most built-in emails can be automatically sent with minimal setup,
    //except "request password reset" requires a function in the User's model.  See readme.md for details
    'send_emails'      => [
        'new_user_registered'    => true,
        'verification'           => true,
        'user_verified'          => true,
        'login'                  => true,
        'password_reset_success' => true,
    ],

];
