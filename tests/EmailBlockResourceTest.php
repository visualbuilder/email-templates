<?php

use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\Testing\TestAction;
use Filament\Forms\Components\Builder;

use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

use Visualbuilder\EmailTemplates\Models\EmailBlock;
use Visualbuilder\EmailTemplates\Resources\EmailBlockResource;
use Visualbuilder\EmailTemplates\Resources\EmailBlockResource\Pages\CreateEmailBlock;
use Visualbuilder\EmailTemplates\Resources\EmailBlockResource\Pages\EditEmailBlock;
use Visualbuilder\EmailTemplates\Resources\EmailBlockResource\Pages\ListEmailBlocks;

function textLayout(string $text): array
{
    return ['a1' => ['type' => 'rich_text', 'data' => ['content' => "<p>{$text}</p>"]]];
}

it('can access the block library list page', function () {
    get(EmailBlockResource::getUrl('index'))->assertSuccessful();
});

it('lists blocks', function () {
    $blocks = collect([
        EmailBlock::create(['name' => 'Signature', 'layout' => textLayout('Sig')]),
        EmailBlock::create(['name' => 'Promo', 'layout' => textLayout('Promo'), 'is_active' => false]),
    ]);

    livewire(ListEmailBlocks::class)->assertCanSeeTableRecords($blocks);
});

it('can access the create and edit pages', function () {
    $block = EmailBlock::create(['name' => 'Signature', 'layout' => textLayout('Sig')]);

    get(EmailBlockResource::getUrl('create'))->assertSuccessful();
    get(EmailBlockResource::getUrl('edit', ['record' => $block]))->assertSuccessful();
});

it('creates a block', function () {
    livewire(CreateEmailBlock::class)
        ->fillForm([
            'name' => 'Signature',
            'description' => 'Team sign-off',
            'layout' => textLayout('Kind regards'),
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $block = EmailBlock::where('name', 'Signature')->first();
    expect($block)->not->toBeNull()
        ->and($block->is_active)->toBeTrue()
        ->and($block->description)->toBe('Team sign-off')
        ->and(array_values($block->layout)[0]['data']['content'])->toBe('<p>Kind regards</p>');
});

it('does not offer saved blocks inside the block library form', function () {
    livewire(CreateEmailBlock::class)
        ->assertFormComponentExists('layout', fn (Builder $builder): bool => $builder->getBlock('saved_block') === null
            && $builder->getBlock('rich_text') !== null);
});

it('requires a unique name and at least one block', function () {
    EmailBlock::create(['name' => 'Signature', 'layout' => textLayout('Sig')]);

    livewire(CreateEmailBlock::class)
        ->fillForm(['name' => 'Signature', 'layout' => []])
        ->call('create')
        ->assertHasFormErrors(['name' => 'unique', 'layout' => 'min']);
});

it('edits a block keeping its own name valid', function () {
    $block = EmailBlock::create(['name' => 'Signature', 'layout' => textLayout('Old')]);

    livewire(EditEmailBlock::class, ['record' => $block->getRouteKey()])
        ->fillForm(['is_active' => false])
        ->set('data.layout', textLayout('New'))
        ->call('save')
        ->assertHasNoFormErrors();

    $block->refresh();
    expect($block->is_active)->toBeFalse()
        ->and($block->layout)->toHaveCount(1)
        ->and(array_values($block->layout)[0]['data']['content'])->toBe('<p>New</p>');
});

it('soft deletes and restores a block', function () {
    $block = EmailBlock::create(['name' => 'Signature', 'layout' => textLayout('Sig')]);

    livewire(EditEmailBlock::class, ['record' => $block->getRouteKey()])
        ->assertActionExists(DeleteAction::class)
        ->callAction(DeleteAction::class);

    $this->assertSoftDeleted($block);

    livewire(ListEmailBlocks::class)
        ->callAction(TestAction::make(RestoreAction::class)->table($block));

    expect($block->fresh()->trashed())->toBeFalse();
});

it('deletes a block from the table and warns that templates send without it', function () {
    $block = EmailBlock::create(['name' => 'Signature', 'layout' => textLayout('Sig')]);

    livewire(ListEmailBlocks::class)
        ->assertActionExists(
            TestAction::make(DeleteAction::class)->table($block),
            fn (DeleteAction $action): bool => $action->getModalDescription() === 'Templates using this block will send without it.',
        )
        ->callAction(TestAction::make(DeleteAction::class)->table($block));

    $this->assertSoftDeleted($block);
});

it('previews a block inside the default theme', function () {
    $this->makeTheme();
    $block = EmailBlock::create(['name' => 'Signature', 'layout' => textLayout('Preview me ##user.name##')]);

    $html = base64_decode($block->getBase64EmailPreviewData());

    expect($html)->toContain('<p>Preview me')
        ->toContain('Signature')
        ->toContain('<!-- SUPPORT CALLOUT -->');

    livewire(EditEmailBlock::class, ['record' => $block->getRouteKey()])
        ->callAction('preview')
        ->assertSuccessful();
});
