<?php

declare(strict_types=1);

namespace Visualbuilder\EmailTemplates\Blocks;

use Visualbuilder\EmailTemplates\Enums\BlockRenderMode;
use Visualbuilder\EmailTemplates\Layout\EmailLayoutRenderer;

/**
 * What a block needs to render itself. $models === null means a Builder
 * preview: tokens are left as they are.
 */
final class BlockRenderContext
{
    /**
     * @param  array<string, mixed>  $theme
     */
    public function __construct(
        public readonly EmailLayoutRenderer $renderer,
        public readonly array $theme,
        public readonly object|array|null $models,
        public readonly BlockRenderMode $mode = BlockRenderMode::Email,
        public readonly int $depth = 0,
    ) {}
}
