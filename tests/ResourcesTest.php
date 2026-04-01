<?php

use Filament\Actions;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;

use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

use Visualbuilder\EmailTemplates\Models\EmailTemplate;
use Visualbuilder\EmailTemplates\Resources\EmailTemplateResource;
use Visualbuilder\EmailTemplates\Resources\EmailTemplateResource\Pages\CreateEmailTemplate;
use Visualbuilder\EmailTemplates\Resources\EmailTemplateResource\Pages\EditEmailTemplate;
use Visualbuilder\EmailTemplates\Resources\EmailTemplateResource\Pages\ListEmailTemplates;

beforeEach(function () {
    //
});

// listing tests
it('can access email template list page', function () {
    get(EmailTemplateResource::getUrl('index'))
        ->assertSuccessful();
});

it('can list email templates', function () {
    $emailTemplates = EmailTemplate::factory()->count(10)->create();

    livewire(ListEmailTemplates::class)
        ->assertCanSeeTableRecords($emailTemplates);
});

// create tests
it('can access email template create page', function () {
    $test =get(EmailTemplateResource::getUrl('create'));

    $test->assertSuccessful();
});

it('can create email template', function () {
    $newData = EmailTemplate::factory()->make();

    $storedData = livewire(CreateEmailTemplate::class)
        ->fillForm([
            'key' => $newData->key,
            'language' => $newData->language,
            'view' => $newData->view,
            'cc' => $newData->cc,
            'bcc' => $newData->bcc,
            //'from' => $newData->from,
            'name' => $newData->name,
            'preheader' => $newData->preheader,
            'subject' => $newData->subject,
            'title' => $newData->title,
            'content' => $newData->content,
            'deleted_at' => $newData->deleted_at,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(EmailTemplate::class, [
        'key' => $storedData->data['key'],
        'language' => $storedData->data['language'],
        'view' => $storedData->data['view'],
        'cc' => $storedData->data['cc'],
        'bcc' => $storedData->data['bcc'],
       //'from' => $storedData->data['from'],
        'name' => $storedData->data['name'],
        'preheader' => $storedData->data['preheader'],
        'subject' => $storedData->data['subject'],
        'title' => $storedData->data['title'],
        'content' => $storedData->data['content'],
        'deleted_at' => $storedData->data['deleted_at'],
    ]);
});

// edit tests
it('can access email template edit page', function () {
    get(EmailTemplateResource::getUrl('edit', [
        'record' => EmailTemplate::factory()->create(),
    ]))->assertSuccessful();
});

it('can update email an email template', function () {
    $data = EmailTemplate::factory()->create();
    $newData = EmailTemplate::factory()->make();

    $updatedData = livewire(EditEmailTemplate::class, [
        'record' => $data->getRouteKey(),
    ])
        ->fillForm([
            'language' => $newData->language,
            'view' => $newData->view,
            'cc' => $newData->cc,
            'bcc' => $newData->bcc,
            'from.email' => $newData->from['email'],
            'from.name' => $newData->from['name'],
            'name' => $newData->name,
            'preheader' => $newData->preheader,
            'subject' => $newData->subject,
            'title' => $newData->title,
            'content' => $newData->content,
            'deleted_at' => $newData->deleted_at,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(EmailTemplate::class, [
        'key' => $updatedData->data['key'],
        'language' => $updatedData->data['language'],
        'view' => $updatedData->data['view'],
        'cc' => $updatedData->data['cc'],
        'bcc' => $updatedData->data['bcc'],
        'name' => $updatedData->data['name'],
        'preheader' => $updatedData->data['preheader'],
        'subject' => $updatedData->data['subject'],
        'title' => $updatedData->data['title'],
        'content' => $updatedData->data['content'],
        'deleted_at' => $updatedData->data['deleted_at'],
    ]);
});

// delete and restore tests
it('can delete email template', function () {
    $emailTemplate = EmailTemplate::factory()->create();

    livewire(EditEmailTemplate::class, [
        'record' => $emailTemplate->getRouteKey(),
    ])->callAction(DeleteAction::class);

    $this->assertSoftDeleted($emailTemplate);
});

it('can restore email template', function () {
    $emailTemplate = EmailTemplate::factory()->create();

    livewire(EditEmailTemplate::class, [
        'record' => $emailTemplate->getRouteKey(),
    ])->callAction(DeleteAction::class);

    livewire(EditEmailTemplate::class, [
        'record' => $emailTemplate->getRouteKey(),
    ])->callAction(RestoreAction::class);

    $this->assertDatabaseHas(EmailTemplate::class, [
        'id' => $emailTemplate->getRouteKey(),
        'deleted_at' => null,
    ]);
});

it('can force delete email template', function () {
    $emailTemplate = EmailTemplate::factory()->create();

    livewire(EditEmailTemplate::class, [
        'record' => $emailTemplate->getRouteKey(),
    ])->callAction(DeleteAction::class);

    livewire(EditEmailTemplate::class, [
        'record' => $emailTemplate->getRouteKey(),
    ])->callAction(ForceDeleteAction::class);

    $this->assertModelMissing($emailTemplate);
});

// preview tests
it('can preview email template', function () {
    $emailTemplate = EmailTemplate::factory()->create();
    $this->makeTheme();
    livewire(EditEmailTemplate::class, [
        'record' => $emailTemplate->getRouteKey(),
    ])->callAction('preview')
    ->assertSuccessful();
});


it('can preview user welcome email', function () {
    $this->makeTheme();
    $emailData = EmailTemplate::factory()->create(['key' => 'user-welcome']);
    livewire(EditEmailTemplate::class, ['record' => $emailData->getRouteKey()])
        ->callAction('preview')
        ->assertSuccessful();
});

it('can preview user password reset request email', function () {
    $this->makeTheme();
    $emailData = EmailTemplate::factory()->create(['key' => 'user-request-reset']);
    livewire(EditEmailTemplate::class, ['record' => $emailData->getRouteKey()])
        ->callAction('preview')
        ->assertSuccessful();
});

it('can preview user password reset success email', function () {
    $this->makeTheme();
    $emailData = EmailTemplate::factory()->create(['key' => 'user-password-reset-success']);
    livewire(EditEmailTemplate::class, ['record' => $emailData->getRouteKey()])
        ->callAction('preview')
        ->assertSuccessful();
});

it('can preview user account locked out email', function () {
    $this->makeTheme();
    $emailData = EmailTemplate::factory()->create(['key' => 'user-locked-out']);
    livewire(EditEmailTemplate::class, ['record' => $emailData->getRouteKey()])
        ->callAction('preview')
        ->assertSuccessful();
});

it('can preview user verify email', function () {
    $this->makeTheme();
    $emailData = EmailTemplate::factory()->create(['key' => 'user-verify-email']);
    livewire(EditEmailTemplate::class, ['record' => $emailData->getRouteKey()])
        ->callAction('preview')
        ->assertSuccessful();
});

it('can preview user verified email', function () {
    $this->makeTheme();
    $emailData = EmailTemplate::factory()->create(['key' => 'user-verified']);
    livewire(EditEmailTemplate::class, ['record' => $emailData->getRouteKey()])
        ->callAction('preview')
        ->assertSuccessful();
});

it('can preview user logged in email', function () {
    $this->makeTheme();
    $emailData = EmailTemplate::factory()->create(['key' => 'user-login']);
    livewire(EditEmailTemplate::class, ['record' => $emailData->getRouteKey()])
        ->callAction('preview')
        ->assertSuccessful();
});

it('deletes previous logo when updated', function () {
    $relativePath = 'media/email-templates/logos/old-logo.png';
    $fullPath = storage_path('app/public/'.$relativePath);
    \Illuminate\Support\Facades\File::ensureDirectoryExists(dirname($fullPath));
    \Illuminate\Support\Facades\File::put($fullPath, 'fake');

    $emailTemplate = EmailTemplate::factory()->create([
        'logo' => $relativePath,
    ]);

    expect(file_exists($fullPath))->toBeTrue();

    $newData = EmailTemplate::factory()->make();
    $newLogoUrl = 'https://example.com/new-logo.png';

    livewire(EditEmailTemplate::class, [
        'record' => $emailTemplate->getRouteKey(),
    ])
        ->fillForm([
            'language' => $newData->language,
            'view' => $newData->view,
            'cc' => $newData->cc,
            'bcc' => $newData->bcc,
            'from.email' => $newData->from['email'],
            'from.name' => $newData->from['name'],
            'name' => $newData->name,
            'preheader' => $newData->preheader,
            'subject' => $newData->subject,
            'title' => $newData->title,
            'content' => $newData->content,
            'deleted_at' => $newData->deleted_at,
            'logo_type' => 'paste_url',
            'logo_url' => $newLogoUrl,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(file_exists($fullPath))->toBeFalse();
});
