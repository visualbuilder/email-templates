<?php

namespace Visualbuilder\EmailTemplates\Tests\Fixtures;

use Filament\Support\Icons\Heroicon;
use Visualbuilder\EmailTemplates\Blocks\AbstractEmailBlock;
use Visualbuilder\EmailTemplates\Blocks\BlockRenderContext;

/** Test-only host block type: no view, render() returns fixed markup. */
class QuoteBlock extends AbstractEmailBlock
{
    public function name(): string
    {
        return 'quote';
    }

    public function label(): string
    {
        return 'Quote';
    }

    public function icon(): Heroicon
    {
        return Heroicon::OutlinedChatBubbleLeft;
    }

    public function schema(): array
    {
        return [];
    }

    public function render(array $data, BlockRenderContext $context): string
    {
        return '<tr><td>quote block</td></tr>';
    }
}
