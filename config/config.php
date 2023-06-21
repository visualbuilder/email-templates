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
    'editor-guards'=>['vbadmin','web']
];