<?php

return [
    'default-locale'=>'en_GB',
    'default-view'=>'preview-email-template',
    'header-colour' => '#4a2725',
    
    //Models who can receive emails
    'recipients'    => [
        '\\App\\Models\\User',
    ],
    
    //Guards who are authorised to edit email templates
    'editor-guards'=>['web'],
    
    /**
     * Allowed config keys which can be inserted into email templates
     * eg use ##config.app.name## in the email template for automatic replacement.
     */
    'config-keys' => [
        'app.name',
        'app.url',
        // Add other safe config keys here.
        // We don't want to allow all config keys they may contain secret keys or credentials
    ],
];