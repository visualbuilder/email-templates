<?php

declare(strict_types=1);

namespace Visualbuilder\EmailTemplates\Resources\Schemas;

use Filament\Forms\Components\Builder;
use Illuminate\Support\Str;
use Visualbuilder\EmailTemplates\Blocks\Contracts\EmailBlockDefinition;
use Visualbuilder\EmailTemplates\Blocks\EmailBlockRegistry;
use Visualbuilder\EmailTemplates\Blocks\Filament\EmailBuilderBlock;

/**
 * The block composer field: one Builder block per registered block type.
 */
class EmailLayoutBuilder
{
    public static function make(string $name = 'layout', bool $allowSavedBlocks = true): Builder
    {
        $registry = app(EmailBlockRegistry::class);
        $definitions = $allowSavedBlocks ? $registry->all() : $registry->nestable();

        return Builder::make($name)
            ->label('Email body')
            ->addActionLabel('Add block')
            ->blockNumbers(false)
            ->blockPreviews()
            ->reorderableWithButtons()
            ->collapsible()
            ->minItems(1)
            ->blocks(collect($definitions)->map(
                fn (EmailBlockDefinition $definition): EmailBuilderBlock => EmailBuilderBlock::make($definition->name())
                    ->label(fn (?array $state): string => filled($summary = $definition->summary($state ?? []))
                        ? $definition->label().': '.Str::limit($summary, 50)
                        : $definition->label())
                    ->icon($definition->icon())
                    ->preview('vb-email-templates::forms.block-preview')
                    ->schema($definition->schema()),
            )->values()->all())
            ->columnSpanFull();
    }
}
