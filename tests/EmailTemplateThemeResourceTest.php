<?php

use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

use Visualbuilder\EmailTemplates\Models\EmailTemplateTheme;
use Visualbuilder\EmailTemplates\Resources\EmailTemplateThemeResource;
use Visualbuilder\EmailTemplates\Models\EmailTemplate;
use Visualbuilder\EmailTemplates\Resources\EmailTemplateThemeResource\Pages\CreateEmailTemplateTheme;
use Visualbuilder\EmailTemplates\Resources\EmailTemplateThemeResource\Pages\EditEmailTemplateTheme;
use Visualbuilder\EmailTemplates\Resources\EmailTemplateThemeResource\Pages\ListEmailTemplateThemes;

// listing tests
it('can access email template theme list page', function () {
    EmailTemplate::factory()->create();
    get(EmailTemplateThemeResource::getUrl('index'))
        ->assertSuccessful();
});

it('can list email template themes', function () {
    EmailTemplate::factory()->create();
    $themes = EmailTemplateTheme::factory()->count(10)->create();

    livewire(ListEmailTemplateThemes::class)
        ->assertCanSeeTableRecords($themes);
});

// create tests
it('can access email template theme create page', function () {
    EmailTemplate::factory()->create();
    get(EmailTemplateThemeResource::getUrl('create'))
        ->assertSuccessful();
});

it('can create email template theme', function () {
    EmailTemplate::factory()->create();
    $colours = [
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

    $newData = EmailTemplateTheme::factory()->make([
        'colours' => $colours,
        'is_default' => true,
    ]);

    $storedData = livewire(CreateEmailTemplateTheme::class)
        ->fillForm([
            'name' => $newData->name,
            'is_default' => $newData->is_default,
            'colours.header_bg_color' => $newData->colours['header_bg_color'],
            'colours.body_bg_color' => $newData->colours['body_bg_color'],
            'colours.content_bg_color' => $newData->colours['content_bg_color'],
            'colours.footer_bg_color' => $newData->colours['footer_bg_color'],
            'colours.callout_bg_color' => $newData->colours['callout_bg_color'],
            'colours.button_bg_color' => $newData->colours['button_bg_color'],
            'colours.body_color' => $newData->colours['body_color'],
            'colours.callout_color' => $newData->colours['callout_color'],
            'colours.button_color' => $newData->colours['button_color'],
            'colours.anchor_color' => $newData->colours['anchor_color'],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(EmailTemplateTheme::class, [
        'name' => $storedData->data['name'],
        'is_default' => $storedData->data['is_default'],
        'colours->header_bg_color' => $newData->colours['header_bg_color'],
        'colours->body_bg_color' => $newData->colours['body_bg_color'],
        'colours->content_bg_color' => $newData->colours['content_bg_color'],
        'colours->footer_bg_color' => $newData->colours['footer_bg_color'],
        'colours->callout_bg_color' => $newData->colours['callout_bg_color'],
        'colours->button_bg_color' => $newData->colours['button_bg_color'],
        'colours->body_color' => $newData->colours['body_color'],
        'colours->callout_color' => $newData->colours['callout_color'],
        'colours->button_color' => $newData->colours['button_color'],
        'colours->anchor_color' => $newData->colours['anchor_color'],
    ]);
});

// edit tests
it('can access email template theme edit page', function () {
    EmailTemplate::factory()->create();
    get(EmailTemplateThemeResource::getUrl('edit', [
        'record' => EmailTemplateTheme::factory()->create(),
    ]))->assertSuccessful();
});

it('can update an email template theme', function () {
    EmailTemplate::factory()->create();
    $theme = EmailTemplateTheme::factory()->create();
    $colours = [
        'header_bg_color' => '#AA88E5',
        'body_bg_color' => '#e4e4e4',
        'content_bg_color' => '#AAAAAA',
        'footer_bg_color' => '#123456',
        'callout_bg_color' => '#CCF107',
        'button_bg_color' => '#CCEB3B',
        'body_color' => '#111111',
        'callout_color' => '#000000',
        'button_color' => '#EEEE11',
        'anchor_color' => '#FF00FF',
    ];
    $newData = EmailTemplateTheme::factory()->make([
        'colours' => $colours,
        'is_default' => false,
    ]);

    $updatedData = livewire(EditEmailTemplateTheme::class, [
        'record' => $theme->getRouteKey(),
    ])
        ->fillForm([
            'name' => $newData->name,
            'is_default' => $newData->is_default,
            'colours.header_bg_color' => $newData->colours['header_bg_color'],
            'colours.body_bg_color' => $newData->colours['body_bg_color'],
            'colours.content_bg_color' => $newData->colours['content_bg_color'],
            'colours.footer_bg_color' => $newData->colours['footer_bg_color'],
            'colours.callout_bg_color' => $newData->colours['callout_bg_color'],
            'colours.button_bg_color' => $newData->colours['button_bg_color'],
            'colours.body_color' => $newData->colours['body_color'],
            'colours.callout_color' => $newData->colours['callout_color'],
            'colours.button_color' => $newData->colours['button_color'],
            'colours.anchor_color' => $newData->colours['anchor_color'],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(EmailTemplateTheme::class, [
        'id' => $theme->id,
        'name' => $updatedData->data['name'],
        'is_default' => $updatedData->data['is_default'],
        'colours->header_bg_color' => $newData->colours['header_bg_color'],
        'colours->body_bg_color' => $newData->colours['body_bg_color'],
        'colours->content_bg_color' => $newData->colours['content_bg_color'],
        'colours->footer_bg_color' => $newData->colours['footer_bg_color'],
        'colours->callout_bg_color' => $newData->colours['callout_bg_color'],
        'colours->button_bg_color' => $newData->colours['button_bg_color'],
        'colours->body_color' => $newData->colours['body_color'],
        'colours->callout_color' => $newData->colours['callout_color'],
        'colours->button_color' => $newData->colours['button_color'],
        'colours->anchor_color' => $newData->colours['anchor_color'],
    ]);
});

it('toggling is_default resets other themes', function () {
    EmailTemplate::factory()->create();
    $defaultTheme = EmailTemplateTheme::factory()->create(['is_default' => true]);
    $otherTheme = EmailTemplateTheme::factory()->create(['is_default' => false]);

    livewire(EditEmailTemplateTheme::class, [
        'record' => $otherTheme->getRouteKey(),
    ])
        ->fillForm([
            'name' => $otherTheme->name,
            'is_default' => true,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(EmailTemplateTheme::class, [
        'id' => $otherTheme->id,
        'is_default' => true,
    ]);

    $this->assertDatabaseHas(EmailTemplateTheme::class, [
        'id' => $defaultTheme->id,
        'is_default' => false,
    ]);
});

