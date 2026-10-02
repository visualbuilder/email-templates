<?php

declare(strict_types=1);

namespace Visualbuilder\EmailTemplates\Blocks\Types;

use Filament\Forms\Components\TextInput;
use Filament\Support\Icons\Heroicon;
use Visualbuilder\EmailTemplates\Blocks\AbstractEmailBlock;

final class TwoColumnBlock extends AbstractEmailBlock
{
    public function name(): string
    {
        return 'two_column';
    }

    public function label(): string
    {
        return 'Two columns';
    }

    public function icon(): Heroicon
    {
        return Heroicon::OutlinedViewColumns;
    }

    public function schema(): array
    {
        return [
            static::imageUpload('left_image')
                ->label('Left image'),
            TextInput::make('left_image_alt')
                ->label('Left image description (alt text)')
                ->maxLength(150)
                ->requiredWith('left_image'),
            static::richEditor('left_content')
                ->label('Left text')
                ->required(),
            static::imageUpload('right_image')
                ->label('Right image'),
            TextInput::make('right_image_alt')
                ->label('Right image description (alt text)')
                ->maxLength(150)
                ->requiredWith('right_image'),
            static::richEditor('right_content')
                ->label('Right text')
                ->required(),
        ];
    }

    public function summary(array $data): ?string
    {
        return static::textSummary($data['left_content'] ?? null);
    }

    protected function tokenFields(): array
    {
        return ['left_content', 'right_content'];
    }

    protected function imageFields(): array
    {
        return ['left_image', 'right_image'];
    }
}
