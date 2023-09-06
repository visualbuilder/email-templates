<?php

namespace Visualbuilder\EmailTemplates\Resources\EmailTemplateThemeResource\Pages;

use Visualbuilder\EmailTemplates\Resources\EmailTemplateThemeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEmailTemplateThemes extends ListRecords
{
    protected static string $resource = EmailTemplateThemeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
