<?php

return [
    //If you wish to customise the table name change this before migration
    'table_name'       => 'vb_email_templates',
    
    //Email templates will be copied to this directory
    //Here you can edit or create your own
    'view_path'        => 'vendor.vb-email-templates.email',
    
    //The default html email template blade file to use
    'default_view'     => 'generic_email',
    
    //Default Email Styling
    'logo'             => 'media/email-templates/logo.png',
    
    //Logo size in pixels -> 200 pixels high is plenty big enough.
    'logo_width'       => '200',
    'logo_height'      => '200',
    
    //Background Colours
    'header_bg_color'  => '#424242',
    'body_bg_color'    => '#f4f4f4',
    'content_bg_color' => '#fefefe',
    'footer_bg_color'  => '#646464',
    'callout_bg_color' => '#d2d2d2',
    
    //Text Colours
    'body_color'       => '#333333',
    'callout_color'    => '#666666',
    'anchor_color'     => '#ff7978',
    
    //Options for alternative languages
    //Note that Laravel default locale is just 'en'
    //We are being more specific to cater for Engrish vs USA languages
    'default_locale'   => 'en_GB',
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
        // Add other safe config keys here.
        // We don't want to allow all config keys they may contain secret keys or credentials
    ],
];