<?php

return [
    //If you wish to customise the table name change this before migration
    'table_name'       => 'vb_email_templates',
    'theme_table_name'       => 'vb_email_templates_themes',


    //This is the app directory where generated Classes should be put
    "mailable_directory" => 'Mail/Visualbuilder/EmailTemplates',

    //Admin Panel Resource Navigation Options
    'navigation'=>[
        'sort'=>50,
        'group'=>'Settings',
    ],

    //Email templates will be copied to resources/views/vendor/vb-email-templates/email
    //default.blade.php is base view that can be customised below
    'default_view'       => 'default',

    'template_view_path'      => 'vb-email-templates::email',

    //Default Logo
    'logo'                    => 'media/email-templates/logo.png',

    //Logo size in pixels -> 200 pixels high is plenty big enough.
    'logo_width'              => '476',
    'logo_height'             => '117',

    //Content Width in Pixels
    'content_width'           => '600',

    //Contact details included in default email templates
    'customer-services'  => ['email' => 'support@yourcompany.com',
                             'phone' => '+441273 455702'],

    //Footer Links
    'links'                   => [
        ['name' => 'Website', 'url' => 'https://yourwebsite.com', 'title' => 'Goto website'],
        ['name' => 'Privacy Policy', 'url' => 'https://yourwebsite.com/privacy-policy', 'title' => 'View Privacy Policy'],
    ],

    //Options for alternative languages
    //Note that Laravel default locale is just 'en' you can use this but
    //we are being more specific to cater for English vs USA languages
    'default_locale'          => 'en_GB',

    //These will be included in the language picker when editing an email template
    'languages'               => [
        'en_GB' => ['display' => 'British', 'flag-icon' => 'gb'],
        'en_US' => ['display' => 'USA', 'flag-icon' => 'us'],
        'es'    => ['display' => 'Español', 'flag-icon' => 'es'],
        'fr'    => ['display' => 'Français', 'flag-icon' => 'fr'],
        'pt'    => ['display' => 'Brasileiro', 'flag-icon' => 'br'],
        'in'    => ['display' => 'Hindi', 'flag-icon' => 'in'],
    ],

    //Notifiable Models who can receive emails
    'recipients'              => [
        '\\App\\Models\\User',
    ],

    /**
     * Allowed config keys which can be inserted into email templates
     * eg use ##config.app.name## in the email template for automatic replacement.
     */
    'config_keys'             => [
        'app.name',
        'app.url',
        'email-templates.customer-services'
        // Add other safe config keys here.
        // We don't want to allow all config keys they may contain secret keys or credentials
    ],

    //Most built-in emails can be automatically sent with minimal setup,
    //except "request password reset" requires a function in the User's model.  See readme.md for details
    'send_emails'             => [
        'new_user_registered'    => true,
        'verification'           => true,
        'user_verified'          => true,
        'login'                  => true,
        'password_reset_success' => true,
    ],

];
