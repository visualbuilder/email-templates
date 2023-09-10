<?php

namespace Visualbuilder\EmailTemplates\Resources\EmailTemplateResource\Pages;

use Filament\Forms\Form;
use Filament\Resources\Pages\ViewRecord;
use Visualbuilder\EmailTemplates\Resources\EmailTemplateResource;

class ViewEmailTemplate extends ViewRecord
{
    protected static string $resource = EmailTemplateResource::class;

    public function form(Form $form): Form
    {
        return static::getResource()::form();
    }
}
