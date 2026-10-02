<?php

namespace Visualbuilder\EmailTemplates\Resources\EmailTemplateResource\Pages;

use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\CreateRecord;
use Visualbuilder\EmailTemplates\Resources\EmailTemplateResource;

class CreateEmailTemplate extends CreateRecord
{
    protected static string $resource = EmailTemplateResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (! ($this->data['use_layout'] ?? false)) {
            $data['layout'] = null;
        }

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        $emailTemplateResource = new EmailTemplateResource();
        $sortedData = $emailTemplateResource->handleLogo($data);

        return static::getModel()::create($sortedData);
    }
}
