<?php

namespace Visualbuilder\EmailTemplates\Resources\EmailTemplateResource\Pages;

use Visualbuilder\EmailTemplates\Resources\EmailTemplateResource;
use Filament\Resources\Pages\ViewRecord;

class PreviewEmailTemplate extends ViewRecord
{
    protected static string $resource = EmailTemplateResource::class;

    protected static string $view;
    
    public function __construct()
    {
        self::$view = 'vb-email-templates::show';
    }
    
}