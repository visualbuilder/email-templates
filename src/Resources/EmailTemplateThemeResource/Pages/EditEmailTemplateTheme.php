<?php

namespace Visualbuilder\EmailTemplates\Resources\EmailTemplateThemeResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Visualbuilder\EmailTemplates\Resources\EmailTemplateThemeResource;

class EditEmailTemplateTheme extends EditRecord
{
    protected static string $resource = EmailTemplateThemeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
