<?php

use Visualbuilder\EmailTemplates\DefaultTokenHelper;

function makeModels(): object {
    $themeColours = [
        'button_bg_color' => '#000000',
        'button_color' => '#ffffff',
    ];

    $models = new stdClass();
    $models->user = (object) ['name' => 'John Doe'];
    $models->emailTemplate = (object) [
        'theme' => (object) ['colours' => $themeColours],
    ];

    return $models;
}

it('only replaces whitelisted config tokens', function () {
    config()->set('filament-email-templates.config_keys', ['app.name']);

    $helper = new DefaultTokenHelper();
    $content = 'App: ##config.app.name## Url: ##config.app.url##';
    $result = $helper->replaceTokens($content, makeModels());

    expect($result)->toContain(config('app.name'))
        ->and($result)->toContain('##config.app.url##');
});

it('replaces model tokens with nested model attributes', function () {
    $helper = new DefaultTokenHelper();
    $content = 'Hi ##user.name##';
    $result = $helper->replaceTokens($content, makeModels());

    expect($result)->toBe('Hi John Doe');
});

it('renders button html when button token is present', function () {
    $helper = new DefaultTokenHelper();
    $content = "{{button url='https://example.com' title='Read'}}";

    $models = makeModels();
    $expected = view('vb-email-templates::email.parts._button', [
        'url' => 'https://example.com',
        'title' => 'Read',
        'data' => ['theme' => $models->emailTemplate->theme->colours],
    ])->render();

    $result = $helper->replaceTokens($content, $models);

    expect(trim($result))->toBe(trim($expected));
});

