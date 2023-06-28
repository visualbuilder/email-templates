<?php

return [
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
    // Form Field Labels
    'field-labels' => [
        'template-name' => 'Template Display Name',
        'template-name-hint' => '(For admin view only)',
        'key' => 'Key',
        'key-hint' => '(Must match each language version)',
        'lang' => 'Language',
        'email-from' => 'Send Email From',
        'email-to' => 'Send Email To User Type',
        'subject' => 'Subject Line',
        'header' => 'Preheader Text',
        'header-hint' => '(Only shows on some email clients)',
        'title' => 'Title',
        'title-hint' => '(Displays large at very top of email)',
        'content' => 'Content',
    ]
];