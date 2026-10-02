<?php

declare(strict_types=1);

namespace Visualbuilder\EmailTemplates\Blocks\Types;

use Filament\Forms\Components\TextInput;
use Filament\Support\Icons\Heroicon;
use Visualbuilder\EmailTemplates\Blocks\AbstractEmailBlock;

final class ImageBlock extends AbstractEmailBlock
{
    public function name(): string
    {
        return 'image';
    }

    public function label(): string
    {
        return 'Image';
    }

    public function icon(): Heroicon
    {
        return Heroicon::OutlinedPhoto;
    }

    public function schema(): array
    {
        return [
            self::imageUpload('image')
                ->label('Image')
                ->required(),
            TextInput::make('alt')
                ->label('Image description (alt text)')
                ->required()
                ->maxLength(150),
            TextInput::make('link_url')
                ->label('Link')
                ->maxLength(2048)
                ->regex(self::URL_PATTERN),
        ];
    }

    public function summary(array $data): ?string
    {
        return filled($data['alt'] ?? null) ? (string) $data['alt'] : null;
    }

    protected function tokenFields(): array
    {
        return ['link_url'];
    }

    protected function imageFields(): array
    {
        return ['image'];
    }
}
