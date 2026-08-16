<?php

use Visualbuilder\EmailTemplates\Models\EmailTemplate;

function makeTemplateWithContent(string $content): EmailTemplate
{
    return EmailTemplate::factory()->create(['content' => $content]);
}

it('wraps bare tokens in content with the badge span', function () {
    $template = makeTemplateWithContent('<p>Dear ##user.first_name##, welcome to ##config.app.name##</p>');

    $this->artisan('filament-email-templates:wrap-tokens')
        ->expectsOutputToContain('Wrapped 2 token(s) across 1 template(s).')
        ->assertSuccessful();

    expect($template->refresh()->content)
        ->toBe('<p>Dear <span class="vb-token" contenteditable="false">##user.first_name##</span>,'
            .' welcome to <span class="vb-token" contenteditable="false">##config.app.name##</span></p>');
});

it('is idempotent and never double-wraps', function () {
    $template = makeTemplateWithContent('<p>Hi ##user.first_name##</p>');

    $this->artisan('filament-email-templates:wrap-tokens')->assertSuccessful();
    $once = $template->refresh()->content;

    $this->artisan('filament-email-templates:wrap-tokens')
        ->expectsOutputToContain('Wrapped 0 token(s) across 0 template(s).')
        ->assertSuccessful();

    expect($template->refresh()->content)->toBe($once)
        ->and(substr_count($once, 'vb-token'))->toBe(1);
});

it('skips tokens inside html attributes and button pseudo tokens', function () {
    $template = makeTemplateWithContent(
        '<p><a href="##config.app.url##" title="x">Visit ##config.app.name##</a></p>'
        ."<p>{{button url='##tokenUrl##' title='Activate'}}</p>"
    );

    $this->artisan('filament-email-templates:wrap-tokens')->assertSuccessful();

    $content = $template->refresh()->content;

    expect($content)->toContain('href="##config.app.url##"')
        ->and($content)->toContain("{{button url='##tokenUrl##' title='Activate'}}")
        ->and($content)->toContain('<span class="vb-token" contenteditable="false">##config.app.name##</span>');
});

it('reports without saving on dry run', function () {
    $original = '<p>Hi ##user.first_name##</p>';
    $template = makeTemplateWithContent($original);

    $this->artisan('filament-email-templates:wrap-tokens', ['--dry-run' => true])
        ->expectsOutputToContain('[dry-run] Wrapped 1 token(s) across 1 template(s).')
        ->assertSuccessful();

    expect($template->refresh()->content)->toBe($original);
});

it('handles templates with null content', function () {
    EmailTemplate::factory()->create(['content' => null]);

    $this->artisan('filament-email-templates:wrap-tokens')
        ->expectsOutputToContain('Wrapped 0 token(s) across 0 template(s).')
        ->assertSuccessful();
});

it('renders wrapped content identically to unwrapped at send time', function () {
    $helper = new \Visualbuilder\EmailTemplates\DefaultTokenHelper;
    $models = new stdClass;
    $models->user = (object) ['first_name' => 'Jane'];

    $template = makeTemplateWithContent('<p>Hi ##user.first_name##</p>');
    $before = $helper->replaceTokens($template->content, $models);

    $this->artisan('filament-email-templates:wrap-tokens')->assertSuccessful();
    $after = $helper->replaceTokens($template->refresh()->content, $models);

    expect($after)->toBe($before)->toBe('<p>Hi Jane</p>');
});
