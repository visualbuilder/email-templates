<?php

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Visualbuilder\EmailTemplates\Blocks\AbstractEmailBlock;
use Visualbuilder\EmailTemplates\Blocks\EmailBlockRegistry;
use Visualbuilder\EmailTemplates\Blocks\Types\DividerBlock;
use Visualbuilder\EmailTemplates\Layout\EmailLayoutRenderer;
use Visualbuilder\EmailTemplates\Models\EmailBlock;
use Visualbuilder\EmailTemplates\Tests\Models\User;

function layoutTheme(): array
{
    return [
        'header_bg_color' => '#1E88E5',
        'body_bg_color' => '#f4f4f4',
        'content_bg_color' => '#FFFFFB',
        'footer_bg_color' => '#34495E',
        'callout_bg_color' => '#FFC107',
        'button_bg_color' => '#FFEB3B',
        'body_color' => '#333333',
        'callout_color' => '#212121',
        'button_color' => '#2A2A11',
        'anchor_color' => '#1E88E5',
    ];
}

function layoutRenderer(): EmailLayoutRenderer
{
    return app(EmailLayoutRenderer::class)->withTheme(layoutTheme());
}

function layoutModels(string $name = 'Ada Lovelace'): object
{
    return (object) ['user' => User::factory()->make(['name' => $name])];
}

it('renders a text block as one row with raw rich text and tokens replaced', function () {
    $html = layoutRenderer()->render([
        'a1' => ['type' => 'rich_text', 'data' => ['content' => '<p>Hello <strong>##user.name##</strong></p>']],
    ], layoutModels());

    expect($html)->toContain('<!-- BLOCK: TEXT -->')
        ->toContain('<p>Hello <strong>Ada Lovelace</strong></p>')
        ->toContain('bgcolor="#FFFFFB"')
        ->toContain('background-color: #FFFFFB')
        ->toContain('max-width: 600px')
        ->toContain('<!--[if (gte mso 9)|(IE)]>')
        ->and(trim(str_replace('<!-- BLOCK: TEXT -->', '', $html)))->toStartWith('<tr>')->toEndWith('</tr>');
});

it('leaves tokens unreplaced when there are no models (builder preview)', function () {
    $html = layoutRenderer()->renderBlock('rich_text', ['content' => '<p>Hi ##user.name##</p>'], null);

    expect($html)->toContain('<p>Hi ##user.name##</p>');
});

it('renders a hero with escaped plain fields, a public image URL and a button', function () {
    Storage::fake('public', ['url' => 'https://cdn.test']);
    config(['filament-email-templates.block_images_disk' => 'public']);

    $html = layoutRenderer()->renderBlock('hero', [
        'image' => 'media/email-templates/blocks/spring.png',
        'image_alt' => 'Spring banner',
        'heading' => 'Spring <script>alert(1)</script> offer for ##user.name##',
        'text' => "Line one\nLine two",
        'button_label' => 'Book now',
        'button_url' => 'https://example.com/book',
    ], layoutModels());

    expect($html)->toContain('<!-- BLOCK: HERO -->')
        ->toContain('Spring &lt;script&gt;alert(1)&lt;/script&gt; offer for Ada Lovelace')
        ->not->toContain('<script>')
        ->toContain('src="https://cdn.test/media/email-templates/blocks/spring.png"')
        ->toContain('alt="Spring banner"')
        ->toContain('width="540"')
        ->toContain('Line one<br />')
        ->toContain('v:roundrect')
        ->toContain('href="https://example.com/book"')
        ->toContain('Book now');
});

it('renders a hero without an image or button when they are empty', function () {
    $html = layoutRenderer()->renderBlock('hero', ['heading' => 'Just a heading'], null);

    expect($html)->toContain('Just a heading')
        ->not->toContain('<img')
        ->not->toContain('v:roundrect');
});

it('renders two columns with ghost tables and column-width images', function () {
    $html = layoutRenderer()->renderBlock('two_column', [
        'left_image' => 'https://images.test/left.png',
        'left_image_alt' => 'Left',
        'left_content' => '<p>Left ##user.name##</p>',
        'right_content' => '<p>Right side</p>',
    ], layoutModels());

    expect($html)->toContain('<!-- BLOCK: TWO COLUMNS -->')
        ->toContain('<p>Left Ada Lovelace</p>')
        ->toContain('<p>Right side</p>')
        ->toContain('<!--[if mso]><table role="presentation" border="0" cellspacing="0" cellpadding="0" width="100%"><tr><td width="280" valign="top"><![endif]-->')
        ->toContain('display: inline-block; width: 100%; max-width: 280px; vertical-align: top;')
        ->toContain('src="https://images.test/left.png"')
        ->toContain('width="260"')
        ->and(substr_count($html, '<img'))->toBe(1);
});

it('follows content_width for block widths', function () {
    config(['filament-email-templates.content_width' => '650']);

    expect(layoutRenderer()->renderBlock('rich_text', ['content' => 'x'], null))->toContain('max-width: 650px')
        ->and(layoutRenderer()->renderBlock('two_column', ['left_content' => 'l', 'right_content' => 'r'], null))->toContain('max-width: 305px');
});

it('renders a bulletproof button with token replaced URL, theme colours and alignment', function () {
    $html = layoutRenderer()->renderBlock('button', [
        'label' => 'Hi ##user.name##',
        'url' => 'https://example.com/?a=1&b=2',
        'align' => 'right',
    ], layoutModels());

    expect($html)->toContain('<!-- BLOCK: BUTTON -->')
        ->toContain('<!--[if mso]>')
        ->toContain('fillcolor="#FFEB3B"')
        ->toContain('color: #2A2A11')
        ->toContain('align="right"')
        ->toContain('href="https://example.com/?a=1&amp;b=2"')
        ->toContain('Hi Ada Lovelace');
});

it('replaces a singular token in a button URL', function () {
    $html = layoutRenderer()->renderBlock('button', ['label' => 'Go', 'url' => '##tokenUrl##', 'align' => 'center'], (object) ['tokenUrl' => 'https://example.com/magic']);

    expect($html)->toContain('href="https://example.com/magic"');
});

it('renders an image block, linked when a URL is set', function () {
    $html = layoutRenderer()->renderBlock('image', [
        'image' => ['https://images.test/photo.png'],
        'alt' => 'A photo',
        'link_url' => 'https://example.com',
    ], null);

    expect($html)->toContain('<!-- BLOCK: IMAGE -->')
        ->toContain('<a href="https://example.com"')
        ->toContain('src="https://images.test/photo.png"')
        ->toContain('alt="A photo"')
        ->toContain('style="display: block; width: 100%; max-width: 540px; height: auto; border: 0; margin: 0 auto;"');
});

it('leaves out an image that is picked but not saved yet', function () {
    $upload = Mockery::mock(TemporaryUploadedFile::class);

    expect(AbstractEmailBlock::imageUrl($upload))->toBeNull()
        ->and(AbstractEmailBlock::imageUrl([]))->toBeNull()
        ->and(AbstractEmailBlock::imageUrl(null))->toBeNull()
        ->and(layoutRenderer()->renderBlock('image', ['image' => $upload, 'alt' => 'x'], null))->not->toContain('<img');
});

it('renders a divider with spacing and an optional line', function () {
    $withLine = layoutRenderer()->renderBlock('divider', ['spacing' => 'large', 'show_line' => true], null);
    $withoutLine = layoutRenderer()->renderBlock('divider', ['spacing' => 'small', 'show_line' => false], null);

    expect($withLine)->toContain('<!-- BLOCK: DIVIDER -->')
        ->toContain('padding: 48px 30px 48px 30px')
        ->toContain('border-top: 1px solid '.DividerBlock::lineColour(layoutTheme()))
        ->and($withoutLine)->toContain('padding: 16px 30px 16px 30px')
        ->toContain('border-top: 0;');
});

it('mixes the divider line colour from the theme, with a fallback', function () {
    expect(DividerBlock::lineColour(['body_color' => '#000000', 'content_bg_color' => '#ffffff']))->toBe('#cccccc')
        ->and(DividerBlock::lineColour(['body_color' => '#333', 'content_bg_color' => '#fff']))->toBe('#d6d6d6')
        ->and(DividerBlock::lineColour([]))->toBe('#e5e7eb');
});

it('renders blocks in layout order and skips malformed items', function () {
    $html = layoutRenderer()->render([
        'b' => ['type' => 'rich_text', 'data' => ['content' => 'FIRST']],
        'c' => ['type' => 'rich_text'],
        'a' => ['type' => 'button', 'data' => ['label' => 'SECOND', 'url' => 'https://example.com']],
        'd' => 'garbage',
    ], null);

    expect(strpos($html, 'FIRST'))->toBeLessThan(strpos($html, 'SECOND'));
});

it('skips an unknown block type with a warning', function () {
    Log::spy();

    $html = layoutRenderer()->render([['type' => 'carousel', 'data' => []], ['type' => 'rich_text', 'data' => ['content' => 'kept']]], null);

    expect($html)->toContain('kept')->not->toContain('carousel');
    Log::shouldHaveReceived('warning')->with('email layout: unknown block type', ['type' => 'carousel'])->once();
});

it('renders a saved block inline with tokens replaced', function () {
    $saved = EmailBlock::create([
        'name' => 'Signature',
        'layout' => [['type' => 'rich_text', 'data' => ['content' => '<p>Thanks ##user.name##</p>']]],
    ]);

    $html = layoutRenderer()->render([
        ['type' => 'rich_text', 'data' => ['content' => 'Body']],
        ['type' => 'saved_block', 'data' => ['email_block_id' => $saved->id]],
    ], layoutModels());

    expect($html)->toContain('Body')->toContain('<p>Thanks Ada Lovelace</p>');
});

it('skips an inactive, deleted or missing saved block', function () {
    Log::spy();
    $inactive = EmailBlock::create(['name' => 'Off', 'is_active' => false, 'layout' => [['type' => 'rich_text', 'data' => ['content' => 'INACTIVE']]]]);
    $deleted = EmailBlock::create(['name' => 'Gone', 'layout' => [['type' => 'rich_text', 'data' => ['content' => 'DELETED']]]]);
    $deleted->delete();

    $html = layoutRenderer()->render([
        ['type' => 'saved_block', 'data' => ['email_block_id' => $inactive->id]],
        ['type' => 'saved_block', 'data' => ['email_block_id' => $deleted->id]],
        ['type' => 'saved_block', 'data' => ['email_block_id' => 9999]],
    ], null);

    expect($html)->toBe('');
    Log::shouldHaveReceived('warning')->with('email layout: saved block missing or inactive', Mockery::any())->times(3);
});

it('ignores a saved block nested inside a saved block', function () {
    Log::spy();
    $inner = EmailBlock::create(['name' => 'Inner', 'layout' => [['type' => 'rich_text', 'data' => ['content' => 'INNER']]]]);
    $outer = EmailBlock::create(['name' => 'Outer', 'layout' => [
        ['type' => 'rich_text', 'data' => ['content' => 'OUTER']],
        ['type' => 'saved_block', 'data' => ['email_block_id' => $inner->id]],
    ]]);

    $html = layoutRenderer()->render([['type' => 'saved_block', 'data' => ['email_block_id' => $outer->id]]], null);

    expect($html)->toContain('OUTER')->not->toContain('INNER');
    Log::shouldHaveReceived('warning')->with('email layout: saved block inside a saved block ignored', ['email_block_id' => $inner->id])->once();
});

it('summarises each block for the builder header', function () {
    $saved = EmailBlock::create(['name' => 'Footer promo', 'layout' => []]);
    $saved->delete();
    $registry = app(EmailBlockRegistry::class);

    expect($registry->get('rich_text')->summary(['content' => '<p>'.str_repeat('a', 60).'</p>']))->toBe(str_repeat('a', 50))
        ->and($registry->get('rich_text')->summary(['content' => '<p> </p>']))->toBeNull()
        ->and($registry->get('hero')->summary(['heading' => 'Spring offer']))->toBe('Spring offer')
        ->and($registry->get('two_column')->summary(['left_content' => '<p>Left&nbsp;text</p>']))->toBe("Left\u{a0}text")
        ->and($registry->get('button')->summary(['label' => 'Book']))->toBe('Book')
        ->and($registry->get('image')->summary(['alt' => 'Photo']))->toBe('Photo')
        ->and($registry->get('divider')->summary(['spacing' => 'large']))->toBe('Large')
        ->and($registry->get('divider')->summary([]))->toBeNull()
        ->and($registry->get('saved_block')->summary(['email_block_id' => $saved->id]))->toBe('Footer promo')
        ->and($registry->get('saved_block')->summary([]))->toBeNull();
});

it('resolves the block image disk from config, then the Filament default, then public', function () {
    config(['filament-email-templates.block_images_disk' => 's3_public', 'filament.default_filesystem_disk' => 'local']);
    expect(AbstractEmailBlock::imageDisk())->toBe('s3_public');

    config(['filament-email-templates.block_images_disk' => null]);
    expect(AbstractEmailBlock::imageDisk())->toBe('local');

    config(['filament.default_filesystem_disk' => null]);
    expect(AbstractEmailBlock::imageDisk())->toBe('public');
});
