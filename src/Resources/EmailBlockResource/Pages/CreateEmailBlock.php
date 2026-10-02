<?php

declare(strict_types=1);

namespace Visualbuilder\EmailTemplates\Resources\EmailBlockResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Visualbuilder\EmailTemplates\Resources\EmailBlockResource;

class CreateEmailBlock extends CreateRecord
{
    protected static string $resource = EmailBlockResource::class;
}
