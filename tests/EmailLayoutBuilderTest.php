<?php

use Filament\Forms\Components\FileUpload;
use Visualbuilder\EmailTemplates\Blocks\EmailBlockRegistry;
use Visualbuilder\EmailTemplates\Blocks\Filament\EmailBuilderBlock;
use Visualbuilder\EmailTemplates\Blocks\Types\ImageBlock;
use Visualbuilder\EmailTemplates\Resources\Schemas\EmailLayoutBuilder;
use Visualbuilder\EmailTemplates\Tests\Fixtures\QuoteBlock;

function builderBlocks($builder): array
{
    return $builder->getDefaultChildComponents();
}

/** The document a block preview iframe loads, decoded from its srcdoc attribute. */
function previewDocument(string $html): string
{
    expect(preg_match('/srcdoc="([^"]*)"/', $html, $match))->toBe(1);

    return html_entity_decode($match[1], ENT_QUOTES | ENT_HTML5);
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

    expect(previewDocument($html))->toContain('Book ##user.name##')
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

    expect(previewDocument($html))->toContain('<p>No theme yet</p>');
});

it('isolates block preview html in a sandboxed iframe', function () {
    $html = EmailBuilderBlock::make('rich_text')
        ->preview('vb-email-templates::forms.block-preview')
        ->renderPreview(['content' => '<p>Hi</p><script>alert(1)</script><img src="x" onerror="alert(2)">'])
        ->render();

    expect($html)->not->toContain('<script>')
        ->not->toContain('<img')
        ->not->toContain('<p>Hi</p>')
        ->toContain('&lt;script&gt;alert(1)&lt;/script&gt;')
        ->toContain('title="Email block preview"')
        ->and(trim($html))->toStartWith('<iframe')
        ->and(preg_match('/<iframe\s+sandbox\s/', $html))->toBe(1)
        ->and($html)->not->toContain('allow-scripts')
        ->not->toContain('allow-same-origin')
        ->and(previewDocument($html))->toContain('<script>alert(1)</script>')
        ->toContain('onerror="alert(2)"');
});

it('accepts only raster images for block image uploads', function () {
    $upload = collect((new ImageBlock)->schema())->first(fn ($component) => $component instanceof FileUpload);

    expect($upload->getAcceptedFileTypes())->toBe(['image/png', 'image/jpeg', 'image/gif', 'image/webp']);
});
