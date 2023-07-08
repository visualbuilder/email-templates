<?php

namespace Visualbuilder\EmailTemplates\Components;

use Filament\Forms\Components\Component;

class Iframe extends Component
{
    protected string $view = 'vb-email-templates::forms.components.iframe';

    public $name;
    public $src;
    public $height;
    public $width;

    public function __construct($name, $label = null, $src = null, $height = '800px', $width = '100%')
    {
        $this->src = $src;
        $this->height = $height;
        $this->width = $width;
        $this->name = $name;
        $this->setUp();
    }

    protected function setUp(): void
    {
        $this->afterStateHydrated(function ($record) {
            $this->src = route('email-template.preview', $record);
        });
    }

    public static function make($name, $label = null, $src = null, $height = '800px', $width = '100%')
    {
        return new static($name, $label, $src, $height, $width);
    }
}
