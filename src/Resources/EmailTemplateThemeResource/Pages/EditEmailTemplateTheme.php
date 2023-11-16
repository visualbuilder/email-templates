<?php

namespace Visualbuilder\EmailTemplates\Resources\EmailTemplateThemeResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Visualbuilder\EmailTemplates\Resources\EmailTemplateThemeResource;
use Illuminate\Database\Eloquent\Model;
use Visualbuilder\EmailTemplates\Models\EmailTemplateTheme;

class EditEmailTemplateTheme extends EditRecord
{
    protected static string $resource = EmailTemplateThemeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $record->update($data);

        if($data['is_default']) {
            EmailTemplateTheme::where('id', '!=', $record->id)
                                ->where('is_default', true)
                                ->update(['is_default' => false]);
        }
        return $record;
    }
}
