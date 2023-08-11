<?php

namespace Visualbuilder\EmailTemplates\Resources\EmailTemplateResource\Pages;

use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Visualbuilder\EmailTemplates\Resources\EmailTemplateResource;

class ListEmailTemplates extends ListRecords
{
    protected static string $resource = EmailTemplateResource::class;
    protected $createMailableHelper;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function createMailClass(): void
    {
        $this->createMailableHelper = app(\Visualbuilder\EmailTemplates\Contracts\CreateMailableInterface::class);
        $notify = $this->createMailableHelper->createMailable($this);
        Notification::make()
            ->title($notify->title)
            ->icon($notify->icon)
            ->iconColor($notify->icon_color)
            ->send();
    }
}
