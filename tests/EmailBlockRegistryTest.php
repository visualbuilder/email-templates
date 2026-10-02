<?php

use Visualbuilder\EmailTemplates\Blocks\EmailBlockRegistry;
use Visualbuilder\EmailTemplates\Blocks\Types\ButtonBlock;
use Visualbuilder\EmailTemplates\Blocks\Types\DividerBlock;
use Visualbuilder\EmailTemplates\Blocks\Types\HeroBlock;
use Visualbuilder\EmailTemplates\Blocks\Types\ImageBlock;
use Visualbuilder\EmailTemplates\Blocks\Types\RichTextBlock;
use Visualbuilder\EmailTemplates\Blocks\Types\SavedBlock;
use Visualbuilder\EmailTemplates\Blocks\Types\TwoColumnBlock;
use Visualbuilder\EmailTemplates\Layout\EmailLayoutRenderer;
use Visualbuilder\EmailTemplates\Tests\Fixtures\QuoteBlock;
use Visualbuilder\EmailTemplates\Tests\Fixtures\ReplacementHeroBlock;

it('lists the seven default block types in menu order keyed by name', function () {
    $all = app(EmailBlockRegistry::class)->all();

    expect(array_keys($all))->toBe(['rich_text', 'hero', 'two_column', 'button', 'image', 'divider', 'saved_block'])
        ->and(array_map(fn ($definition) => $definition::class, array_values($all)))->toBe(EmailBlockRegistry::DEFAULT_TYPES)
        ->and(EmailBlockRegistry::DEFAULT_TYPES)->toBe([
            RichTextBlock::class,
            HeroBlock::class,
            TwoColumnBlock::class,
            ButtonBlock::class,
            ImageBlock::class,
            DividerBlock::class,
            SavedBlock::class,
        ]);
});

it('falls back to the default types when the config key is empty', function () {
    config(['filament-email-templates.block_types' => []]);

    expect(array_keys((new EmailBlockRegistry)->all()))->toHaveCount(7);
});

it('excludes saved blocks from the nestable types', function () {
    $nestable = app(EmailBlockRegistry::class)->nestable();

    expect($nestable)->not->toHaveKey('saved_block')
        ->and(array_keys($nestable))->toBe(['rich_text', 'hero', 'two_column', 'button', 'image', 'divider']);
});

it('adds a host block registered at runtime last and renders it', function () {
    $registry = app(EmailBlockRegistry::class);
    $registry->all();

    $registry->register(QuoteBlock::class);

    expect(array_key_last($registry->all()))->toBe('quote')
        ->and(app(EmailLayoutRenderer::class)->render([['type' => 'quote', 'data' => []]], null))
        ->toBe('<tr><td>quote block</td></tr>');
});

it('adds a host block listed in the block_types config', function () {
    config(['filament-email-templates.block_types' => [...EmailBlockRegistry::DEFAULT_TYPES, QuoteBlock::class]]);

    expect(array_keys((new EmailBlockRegistry)->all()))->toContain('quote')
        ->and(array_key_last((new EmailBlockRegistry)->all()))->toBe('quote');
});

it('replaces a package block in place when a later class has the same name', function () {
    $registry = (new EmailBlockRegistry)->register(ReplacementHeroBlock::class);

    expect(array_keys($registry->all()))->toBe(['rich_text', 'hero', 'two_column', 'button', 'image', 'divider', 'saved_block'])
        ->and($registry->get('hero'))->toBeInstanceOf(ReplacementHeroBlock::class);
});

it('rejects a class that does not implement the contract', function () {
    (new EmailBlockRegistry)->register(stdClass::class)->all();
})->throws(InvalidArgumentException::class, 'stdClass must implement EmailBlockDefinition');

it('returns null for an unknown block name', function () {
    expect(app(EmailBlockRegistry::class)->get('nope'))->toBeNull();
});
