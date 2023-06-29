<?php

use Visualbuilder\EmailTemplates\Resources\EmailTemplateResource;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

it('can render Email Template List', function () {
    get(EmailTemplateResource::getUrl('index'))
        ->assertSuccessful();
});

