<?php

use Visualbuilder\EmailTemplates\Blocks\EmailBlockRegistry;
use Visualbuilder\EmailTemplates\Blocks\Filament\EmailBuilderBlock;
use Visualbuilder\EmailTemplates\Resources\Schemas\EmailLayoutBuilder;
use Visualbuilder\EmailTemplates\Tests\Fixtures\QuoteBlock;

function builderBlocks($builder): array
{
    return $builder->getDefaultChildComponents();
}

it('has one builder block per registered block type', function () {
    $blocks = builderBlocks(EmailLayoutBuilder::make());

    expect(array_map(fn (EmailBuilderBlock $block) => $block->getName(), $blocks))
        ->toBe(array_keys(app(EmailBlockRegistry::class)->all()));
});

it('leaves saved blocks out when saved blocks are not allowed', function () {
    $names = array_map(fn ($block) => $block->getName(), builderBlocks(EmailLayoutBuilder::make('layout', allowSavedBlocks: false)));

    expect($names)->not->toContain('saved_block')->toContain('rich_text');
});

it('offers host block types added to the registry', function () {
    app(EmailBlockRegistry::class)->register(QuoteBlock::class);

    $names = array_map(fn ($block) => $block->getName(), builderBlocks(EmailLayoutBuilder::make()));

    expect(end($names))->toBe('quote');
});

it('labels a block with its summary and plain in the picker', function () {
    $hero = collect(builderBlocks(EmailLayoutBuilder::make()))->first(fn ($block) => $block->getName() === 'hero');

    expect($hero->getLabel(['heading' => 'Spring offer']))->toBe('Hero: Spring offer')
        ->and($hero->getLabel(['heading' => '']))->toBe('Hero')
        ->and($hero->getLabel(null))->toBe('Hero')
        ->and($hero->getLabel(['heading' => str_repeat('x', 80)]))->toBe('Hero: '.str_repeat('x', 50).'...');
});

it('previews a block through the email renderer with tokens left in place', function () {
    $this->makeTheme();

    $html = EmailBuilderBlock::make('button')
        ->preview('vb-email-templates::forms.block-preview')
        ->renderPreview(['label' => 'Book ##user.name##', 'url' => 'https://example.com', 'align' => 'center'])
        ->render();

    expect($html)->toContain('Book ##user.name##')
        ->toContain('<!-- BLOCK: BUTTON -->')
        ->toContain('v:roundrect')
        ->toContain('fillcolor="#FFEB3B"')
        ->toContain('max-width: 600px; margin: 0 auto;');
});

it('previews a block without a default theme', function () {
    $html = EmailBuilderBlock::make('rich_text')
        ->preview('vb-email-templates::forms.block-preview')
        ->renderPreview(['content' => '<p>No theme yet</p>'])
        ->render();

    expect($html)->toContain('<p>No theme yet</p>');
});
