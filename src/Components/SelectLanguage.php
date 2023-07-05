<?php

namespace Visualbuilder\EmailTemplates\Components;

use Filament\Forms\Components\Concerns\HasOptions;
use Filament\Forms\Components\Field;

class SelectLanguage extends Field
{
    use HasOptions;
    
    protected string $view = 'vb-email-templates::forms.components.select-language';
    
}
