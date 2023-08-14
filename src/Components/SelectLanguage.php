<?php

namespace Visualbuilder\EmailTemplates\Components;

use Filament\Forms\Components\Concerns;
use Filament\Forms\Components\Concerns\HasOptions;
use Filament\Forms\Components\Field;
use Filament\Support\Concerns\HasExtraAlpineAttributes;

class SelectLanguage extends Field
{
    use HasOptions;
    // use Concerns\CanBeAutocapitalized;
    // use Concerns\CanBeAutocompleted;
    // use Concerns\CanBeLengthConstrained;
    // use Concerns\CanBeReadOnly;
    // use Concerns\HasAffixes;
    // use Concerns\HasExtraInputAttributes;
    // use Concerns\HasInputMode;
    // use Concerns\HasPlaceholder;
    use HasExtraAlpineAttributes;

    protected string $view = 'vb-email-templates::forms.components.select-language';

}
