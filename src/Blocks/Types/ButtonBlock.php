<?php

declare(strict_types=1);

namespace Visualbuilder\EmailTemplates\Blocks\Types;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Support\Icons\Heroicon;
use Visualbuilder\EmailTemplates\Blocks\AbstractEmailBlock;

final class ButtonBlock extends AbstractEmailBlock
{
    public function name(): string
    {
        return 'button';
    }

    public function label(): string
    {
        return 'Button';
    }

    public function icon(): Heroicon
    {
        return Heroicon::OutlinedCursorArrowRays;
    }

    public function schema(): array
    {
        return [
            TextInput::make('label')
                ->label('Button label')
                ->required()
                ->maxLength(40),
            TextInput::make('url')
                ->label('Button link')
                ->required()
                ->maxLength(2048)
                ->regex(self::URL_PATTERN),
            Select::make('align')
                ->label('Alignment')
                ->options(['left' => 'Left', 'center' => 'Centre', 'right' => 'Right'])
                ->default('center')
                ->required(),
        ];
    }

    public function summary(array $data): ?string
    {
        return filled($data['label'] ?? null) ? (string) $data['label'] : null;
    }

    protected function tokenFields(): array
    {
        return ['label', 'url'];
    }
}
