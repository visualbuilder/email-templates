<?php

declare(strict_types=1);

namespace Visualbuilder\EmailTemplates\Blocks;

use InvalidArgumentException;
use Visualbuilder\EmailTemplates\Blocks\Contracts\EmailBlockDefinition;
use Visualbuilder\EmailTemplates\Blocks\Types\ButtonBlock;
use Visualbuilder\EmailTemplates\Blocks\Types\DividerBlock;
use Visualbuilder\EmailTemplates\Blocks\Types\HeroBlock;
use Visualbuilder\EmailTemplates\Blocks\Types\ImageBlock;
use Visualbuilder\EmailTemplates\Blocks\Types\RichTextBlock;
use Visualbuilder\EmailTemplates\Blocks\Types\SavedBlock;
use Visualbuilder\EmailTemplates\Blocks\Types\TwoColumnBlock;

/**
 * The block types offered by the email block composer.
 *
 * Sources, in order:
 * - config('filament-email-templates.block_types'), or DEFAULT_TYPES when unset/empty
 * - runtime registrations via register()
 *
 * A later class with the same name() replaces the earlier one in the same
 * position, so a host can override a package block.
 */
class EmailBlockRegistry
{
    /** @var array<int, class-string<EmailBlockDefinition>> The order of the "Add block" menu. */
    public const DEFAULT_TYPES = [
        RichTextBlock::class,
        HeroBlock::class,
        TwoColumnBlock::class,
        ButtonBlock::class,
        ImageBlock::class,
        DividerBlock::class,
        SavedBlock::class,
    ];

    /** @var array<int, string> */
    protected array $registered = [];

    /** @var array<string, EmailBlockDefinition>|null */
    protected ?array $definitions = null;

    public function register(string $class): static
    {
        $this->registered[] = $class;
        $this->definitions = null;

        return $this;
    }

    /**
     * @return array<string, EmailBlockDefinition> keyed by name()
     */
    public function all(): array
    {
        if ($this->definitions !== null) {
            return $this->definitions;
        }

        $classes = array_merge(
            config('filament-email-templates.block_types') ?: self::DEFAULT_TYPES,
            $this->registered,
        );

        $definitions = [];

        foreach ($classes as $class) {
            $definition = app($class);

            if (! $definition instanceof EmailBlockDefinition) {
                throw new InvalidArgumentException("{$class} must implement EmailBlockDefinition");
            }

            // Assigning to an existing key keeps its position.
            $definitions[$definition->name()] = $definition;
        }

        return $this->definitions = $definitions;
    }

    public function get(string $name): ?EmailBlockDefinition
    {
        return $this->all()[$name] ?? null;
    }

    /**
     * Block types that may be used inside a saved block.
     *
     * @return array<string, EmailBlockDefinition>
     */
    public function nestable(): array
    {
        return array_filter($this->all(), fn (EmailBlockDefinition $definition): bool => $definition->nestable());
    }
}
