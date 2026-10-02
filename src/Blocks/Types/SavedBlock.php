<?php

declare(strict_types=1);

namespace Visualbuilder\EmailTemplates\Blocks\Types;

use Filament\Forms\Components\Select;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Log;
use Visualbuilder\EmailTemplates\Blocks\AbstractEmailBlock;
use Visualbuilder\EmailTemplates\Blocks\BlockRenderContext;
use Visualbuilder\EmailTemplates\Enums\BlockRenderMode;
use Visualbuilder\EmailTemplates\Models\EmailBlock;

/**
 * A reference to a block from the block library. Rendered inline from the
 * library's current layout, so editing the library block updates every
 * template that uses it. Saved blocks cannot be nested.
 */
final class SavedBlock extends AbstractEmailBlock
{
    public function name(): string
    {
        return 'saved_block';
    }

    public function label(): string
    {
        return 'Saved block';
    }

    public function icon(): Heroicon
    {
        return Heroicon::OutlinedSquares2x2;
    }

    public function schema(): array
    {
        return [
            Select::make('email_block_id')
                ->label('Saved block')
                ->options(fn (): array => EmailBlock::active()->orderBy('name')->pluck('name', 'id')->all())
                ->searchable()
                ->required(),
        ];
    }

    public function summary(array $data): ?string
    {
        return EmailBlock::withTrashed()->find($data['email_block_id'] ?? null)?->name;
    }

    public function nestable(): bool
    {
        return false;
    }

    public function view(BlockRenderMode $mode): ?string
    {
        return null;
    }

    public function render(array $data, BlockRenderContext $context): string
    {
        if ($context->depth >= 1) {
            Log::warning('email layout: saved block inside a saved block ignored', ['email_block_id' => $data['email_block_id'] ?? null]);

            return '';
        }

        $block = EmailBlock::active()->find($data['email_block_id'] ?? null);

        if ($block === null) {
            Log::warning('email layout: saved block missing or inactive', ['email_block_id' => $data['email_block_id'] ?? null]);

            return '';
        }

        return $context->renderer->render($block->layout ?? [], $context->models, $context->depth + 1);
    }
}
