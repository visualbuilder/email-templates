<?php

namespace Visualbuilder\EmailTemplates\Resources\EmailTemplateResource\Pages;

use Visualbuilder\EmailTemplates\Resources\EmailTemplateResource;
use Filament\Resources\Pages\ViewRecord;

class PreviewEmailTemplate extends ViewRecord
{
    protected static string $resource = EmailTemplateResource::class;

    protected static string $view = 'vendor.visual-builder.email-templates.preview-email-template';
}
