<?php

namespace Visualbuilder\EmailTemplates\Resources\EmailTemplateResource\Pages;

use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\ViewRecord;
use Visualbuilder\EmailTemplates\Resources\EmailTemplateResource;

class PreviewEmailTemplate extends ViewRecord
{
    protected static string $resource = EmailTemplateResource::class;

    protected static string $view;
    
    public function __construct()
    {
        self::$view = 'vb-email-templates::show';
    }
    
}