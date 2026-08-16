<?php

use Visualbuilder\EmailTemplates\DefaultTokenHelper;
use Visualbuilder\EmailTemplates\Resources\EmailTemplateResource;

use function Pest\Laravel\get;

function badgeModels(): object
{
    $models = new stdClass;
    $models->user = (object) ['name' => 'John Doe', 'email' => 'john@example.com'];

    return $models;
}

it('strips token badge wrappers before replacement', function () {
    $helper = new DefaultTokenHelper;

    $content = '<p>Hi <span class="vb-token" contenteditable="false">##user.name##</span>&nbsp;welcome</p>';

    expect($helper->replaceTokens($content, badgeModels()))
        ->toBe('<p>Hi John Doe&nbsp;welcome</p>');
});

it('strips multiple badges including config tokens', function () {
    config()->set('filament-email-templates.config_keys', ['app.name']);

    $helper = new DefaultTokenHelper;

    $content = '<span contenteditable="false" class="mce-item vb-token">##user.name##</span>'
        .' uses <span class="vb-token">##config.app.name##</span>';

    $result = $helper->replaceTokens($content, badgeModels());

    expect($result)->toBe('John Doe uses '.config('app.name'))
        ->and($result)->not->toContain('vb-token');
});

it('leaves unrelated spans untouched', function () {
    $helper = new DefaultTokenHelper;

    $content = '<span class="highlight">Hi</span> ##user.name##';

    expect($helper->replaceTokens($content, badgeModels()))
        ->toBe('<span class="highlight">Hi</span> John Doe');
});

it('registers the email-template editor profile with the vbtokens plugin', function () {
    $profile = config('filament-tinyeditor.profiles.email-template');

    expect($profile)->not->toBeNull()
        ->and($profile['toolbar'])->toStartWith('vbtokens | ')
        ->and($profile['plugins'])->toContain('vbtokens')
        ->and($profile['external_plugins']['vbtokens'])->toContain('vendor/filament-email-templates/tiny-plugins/vbtokens.js');
});

it('feeds the token catalogue to the content editor on the create page', function () {
    $response = get(EmailTemplateResource::getUrl('create'));

    $response->assertSuccessful()
        ->assertSee('vbtokens_list', false)
        ->assertSee('##user.email##', false);
});
