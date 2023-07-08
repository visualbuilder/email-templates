<?php

namespace Visualbuilder\EmailTemplates\Resources\EmailTemplateResource\Pages;

use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;
use Visualbuilder\EmailTemplates\Resources\EmailTemplateResource;

class ListEmailTemplates extends ListRecords
{
    protected static string $resource = EmailTemplateResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
