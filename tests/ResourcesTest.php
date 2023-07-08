<?php

use Filament\Pages\Actions\DeleteAction;
use Filament\Pages\Actions\ForceDeleteAction;
use Filament\Pages\Actions\RestoreAction;

use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

use Visualbuilder\EmailTemplates\Models\EmailTemplate;
use Visualbuilder\EmailTemplates\Resources\EmailTemplateResource;
use Visualbuilder\EmailTemplates\Resources\EmailTemplateResource\Pages\CreateEmailTemplate;
use Visualbuilder\EmailTemplates\Resources\EmailTemplateResource\Pages\EditEmailTemplate;
use Visualbuilder\EmailTemplates\Resources\EmailTemplateResource\Pages\ListEmailTemplates;

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
    get(EmailTemplateResource::getUrl('create'))
        ->assertSuccessful();
});

it('can create email template', function () {
    $newData = EmailTemplate::factory()->make();
    $storedData = livewire(CreateEmailTemplate::class)
        ->fillForm([
            // key will get autogenerated w.r.t. name
            'language' => $newData->language,
            'view' => $newData->view,
            'cc' => $newData->cc,
            'bcc' => $newData->bcc,
            'send_to' => $newData->send_to,
            'from' => $newData->from,
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
        'send_to' => $storedData->data['send_to'],
        'from' => $storedData->data['from'],
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
    $newData = EmailTemplate::factory()->create();
    get(EmailTemplateResource::getUrl('edit', $newData->id))
        ->assertSuccessful();
});

it('can update email template', function () {
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
            'send_to' => $newData->send_to,
            'from' => $newData->from,
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
        'send_to' => $updatedData->data['send_to'],
        'from' => $updatedData->data['from'],
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
    ])->callPageAction(DeleteAction::class);

    $this->assertSoftDeleted($emailTemplate);
});

it('can restore email template', function () {
    $emailTemplate = EmailTemplate::factory()->create();

    livewire(EditEmailTemplate::class, [
        'record' => $emailTemplate->getRouteKey(),
    ])->callPageAction(DeleteAction::class);

    livewire(EditEmailTemplate::class, [
        'record' => $emailTemplate->getRouteKey(),
    ])->callPageAction(RestoreAction::class);

    $this->assertDatabaseHas(EmailTemplate::class, [
        'id' => $emailTemplate->getRouteKey(),
        'deleted_at' => null,
    ]);
});

it('can force delete email template', function () {
    $emailTemplate = EmailTemplate::factory()->create();

    livewire(EditEmailTemplate::class, [
        'record' => $emailTemplate->getRouteKey(),
    ])->callPageAction(DeleteAction::class);

    livewire(EditEmailTemplate::class, [
        'record' => $emailTemplate->getRouteKey(),
    ])->callPageAction(ForceDeleteAction::class);

    $this->assertModelMissing($emailTemplate);
});

// preview tests
it('can preview email template', function () {
    get(EmailTemplateResource::getUrl('view', [
        'record' => EmailTemplate::factory()->create(),
    ]))->assertSuccessful();
});
