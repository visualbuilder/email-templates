<?php

declare(strict_types=1);

namespace Visualbuilder\EmailTemplates\Blocks\Types;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Support\Icons\Heroicon;
use Visualbuilder\EmailTemplates\Blocks\AbstractEmailBlock;

final class HeroBlock extends AbstractEmailBlock
{
    public function name(): string
    {
        return 'hero';
    }

    public function label(): string
    {
        return 'Hero';
    }

    public function icon(): Heroicon
    {
        return Heroicon::OutlinedPhoto;
    }

    public function schema(): array
    {
        return [
            static::imageUpload('image')
                ->label('Image'),
            TextInput::make('image_alt')
                ->label('Image description (alt text)')
                ->maxLength(150)
                ->requiredWith('image'),
            TextInput::make('heading')
                ->label('Heading')
                ->required()
                ->maxLength(120),
            Textarea::make('text')
                ->label('Text')
                ->maxLength(500)
                ->rows(3),
            TextInput::make('button_label')
                ->label('Button label')
                ->maxLength(40)
                ->requiredWith('button_url'),
            TextInput::make('button_url')
                ->label('Button link')
                ->maxLength(2048)
                ->requiredWith('button_label')
                ->regex(self::URL_PATTERN),
        ];
    }

    public function summary(array $data): ?string
    {
        return filled($data['heading'] ?? null) ? (string) $data['heading'] : null;
    }

    protected function tokenFields(): array
    {
        return ['heading', 'text', 'button_label', 'button_url'];
    }

    protected function imageFields(): array
    {
        return ['image'];
    }
}
