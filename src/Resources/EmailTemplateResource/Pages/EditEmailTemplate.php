<?php

namespace Visualbuilder\EmailTemplates\Resources\EmailTemplateResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\View\View;
use Visualbuilder\EmailTemplates\Models\EmailTemplate;
use Visualbuilder\EmailTemplates\Resources\EmailTemplateResource;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;

class EditEmailTemplate extends EditRecord
{
    protected static string $resource = EmailTemplateResource::class;

    protected function getActions(): array
    {
        return [
                Actions\Action::make('back')->label(__('Back'))
                        ->url(EmailTemplateResource::getUrl())
            ,
                Actions\Action::make('preview')->label(__('Preview'))->modalContent(fn (EmailTemplate $record): View => view(
                        'vb-email-templates::forms.components.iframe',
                        ['record' => $record],
                ))->form(null),
                Actions\Action::make('clear_all_caches')
                        ->label(__('Clear All Caches'))
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading(__('Clear All Template Caches'))
                        ->modalDescription(__('This will clear all caches for this email template, ensuring the latest changes are immediately visible. Use this if you\'ve updated the template but the changes aren\'t appearing.'))
                        ->action(function (EmailTemplate $record) {
                            EmailTemplate::clearEmailTemplateCache($record->key, $record->language);
                            \Filament\Notifications\Notification::make()
                                    ->title(__('Caches Cleared'))
                                    ->body(__('All caches for this template have been cleared successfully. The updated template will now be used immediately.'))
                                    ->success()
                                    ->send();
                        }),
                Actions\DeleteAction::make(),
                Actions\ForceDeleteAction::make()
                        ->before(function (EmailTemplate $record, EmailTemplateResource $emailTemplateResource) {
                            $emailTemplateResource->handleLogoDelete($record->logo);
                        }),
                Actions\RestoreAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['logo_type'] = 'browse_another';

        if(!is_null($data['logo']) && Str::isUrl($data['logo'])) {
            $data['logo_type'] = 'paste_url';
            $data['logo_url'] = $data['logo'];
        }

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $emailTemplateResource = new EmailTemplateResource();
        $sortedData = $emailTemplateResource->handleLogo($data);

        // deleting previous logo
        if ($record->logo != ($sortedData['logo'] ?? null)) {
            $emailTemplateResource->handleLogoDelete($record->logo);
        }

        $record->update($sortedData);

        return $record;
    }
}
