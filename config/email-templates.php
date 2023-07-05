<?php

return [
    //If you wish to customise the table name change this before migration
    'table_name'       => 'vb_email_templates',
    
    //Email templates will be copied to resources/views/vendor/vb-email-templates/email
    //This is the default html email template blade file to use when creating new content
    'default_view'     => 'vb-email-templates::default',

    'template_view_path'   => 'vb-email-templates::email-templates',
    
    //Default Email Styling
    'logo'             => 'media/email-templates/logo.png',
    
    //Logo size in pixels -> 200 pixels high is plenty big enough.
    'logo_width'       => '200',
    'logo_height'      => '200',
    
    //Content Width in Pixels
    'content_width'    => '600',
    
    //Background Colours
    'header_bg_color'  => '#424242',
    'body_bg_color'    => '#f4f4f4',
    'content_bg_color' => '#fefefe',
    'footer_bg_color'  => '#646464',
    'callout_bg_color' => '#700000',
    
    //Text Colours
    'body_color'       => '#333333',
    'callout_color'    => '#ffffff',
    'anchor_color'     => '#ff7978',
    
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
    
    //Models who can receive emails
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
    
    'customer-services'=>'support@yourcompany.com'
];