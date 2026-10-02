<?php

declare(strict_types=1);

namespace Visualbuilder\EmailTemplates\Blocks\Types;

use Filament\Support\Icons\Heroicon;
use Visualbuilder\EmailTemplates\Blocks\AbstractEmailBlock;

final class RichTextBlock extends AbstractEmailBlock
{
    public function name(): string
    {
        return 'rich_text';
    }

    public function label(): string
    {
        return 'Text';
    }

    public function icon(): Heroicon
    {
        return Heroicon::OutlinedDocumentText;
    }

    public function schema(): array
    {
        return [
            self::richEditor('content')
                ->label('Text')
                ->required(),
        ];
    }

    public function summary(array $data): ?string
    {
        return self::textSummary($data['content'] ?? null);
    }

    protected function tokenFields(): array
    {
        return ['content'];
    }
}
