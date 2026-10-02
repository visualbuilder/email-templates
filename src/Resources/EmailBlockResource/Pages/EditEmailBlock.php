<?php

declare(strict_types=1);

namespace Visualbuilder\EmailTemplates\Resources\EmailBlockResource\Pages;

use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\View\View;
use Visualbuilder\EmailTemplates\Models\EmailBlock;
use Visualbuilder\EmailTemplates\Resources\EmailBlockResource;

class EditEmailBlock extends EditRecord
{
    protected static string $resource = EmailBlockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('preview')
                ->label(__('Preview'))
                ->slideOver()
                ->modalSubmitAction(false)
                ->modalContent(fn (EmailBlock $record): View => view('vb-email-templates::forms.components.iframe', ['record' => $record])),
            DeleteAction::make()
                ->modalDescription(__('Templates using this block will send without it.')),
            RestoreAction::make(),
        ];
    }
}
