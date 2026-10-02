<?php

declare(strict_types=1);

namespace Visualbuilder\EmailTemplates\Layout;

use Illuminate\Support\Facades\Log;
use Visualbuilder\EmailTemplates\Blocks\BlockRenderContext;
use Visualbuilder\EmailTemplates\Blocks\EmailBlockRegistry;
use Visualbuilder\EmailTemplates\Enums\BlockRenderMode;

/**
 * Renders a block layout (Builder state: [uuid => ['type' => ..., 'data' => [...]]])
 * to email table rows. Used by the send path, the template preview and the
 * Builder block previews, so all three produce the same markup.
 */
class EmailLayoutRenderer
{
    /** @var array<string, mixed> */
    protected array $theme = [];

    public function __construct(protected EmailBlockRegistry $registry) {}

    /**
     * @param  array<string, mixed>  $colours
     */
    public function withTheme(array $colours): static
    {
        $clone = clone $this;
        $clone->theme = $colours;

        return $clone;
    }

    /**
     * @param  array<array-key, mixed>  $layout
     */
    public function render(array $layout, object|array|null $models, int $depth = 0): string
    {
        $html = '';

        foreach (array_values($layout) as $item) {
            if (! is_array($item) || ! is_string($item['type'] ?? null) || ! is_array($item['data'] ?? null)) {
                continue;
            }

            $html .= $this->renderBlock($item['type'], $item['data'], $models, $depth);
        }

        return $html;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function renderBlock(string $type, array $data, object|array|null $models, int $depth = 0): string
    {
        $definition = $this->registry->get($type);

        if ($definition === null) {
            Log::warning('email layout: unknown block type', ['type' => $type]);

            return '';
        }

        return $definition->render($data, new BlockRenderContext($this, $this->theme, $models, BlockRenderMode::Email, $depth));
    }
}
