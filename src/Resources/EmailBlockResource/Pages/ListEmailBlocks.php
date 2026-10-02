<?php

declare(strict_types=1);

namespace Visualbuilder\EmailTemplates\Resources\EmailBlockResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Visualbuilder\EmailTemplates\Resources\EmailBlockResource;

class ListEmailBlocks extends ListRecords
{
    protected static string $resource = EmailBlockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
