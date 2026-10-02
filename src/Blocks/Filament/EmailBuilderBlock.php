<?php

declare(strict_types=1);

namespace Visualbuilder\EmailTemplates\Blocks\Filament;

use Filament\Forms\Components\Builder\Block;
use Illuminate\Contracts\View\View;

/**
 * Builder block whose preview goes through EmailLayoutRenderer. Filament
 * passes only the item's data to a preview, so the block type is added here
 * and one shared preview view serves every block type.
 */
class EmailBuilderBlock extends Block
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function renderPreview(array $data): View
    {
        return view('vb-email-templates::forms.block-preview', ['type' => $this->getName(), 'data' => $data]);
    }
}
