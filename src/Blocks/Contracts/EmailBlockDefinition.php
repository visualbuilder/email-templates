<?php

declare(strict_types=1);

namespace Visualbuilder\EmailTemplates\Blocks\Contracts;

use Filament\Support\Icons\Heroicon;
use Visualbuilder\EmailTemplates\Blocks\BlockRenderContext;
use Visualbuilder\EmailTemplates\Enums\BlockRenderMode;

/**
 * One block type of the email block composer. EmailLayoutBuilder (the form)
 * and EmailLayoutRenderer (the output) both read block types from
 * EmailBlockRegistry, so a new type is one class plus one email view.
 */
interface EmailBlockDefinition
{
    /** Stored type key, e.g. 'hero'. Unique in the registry; never renamed once templates use it. */
    public function name(): string;

    public function label(): string;

    public function icon(): Heroicon;

    /** @return array<int, \Filament\Schemas\Components\Component> */
    public function schema(): array;

    /** Blade view for the mode, or null when the block has no view of its own in that mode. */
    public function view(BlockRenderMode $mode): ?string;

    /**
     * Short text shown after the label in the Builder header (collapsed or not), or null.
     *
     * @param  array<string, mixed>  $data
     */
    public function summary(array $data): ?string;

    /** False when the block may not be used inside a saved block (only SavedBlock). */
    public function nestable(): bool;

    /** @param  array<string, mixed>  $data */
    public function render(array $data, BlockRenderContext $context): string;
}
