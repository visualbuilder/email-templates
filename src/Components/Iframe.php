<?php

namespace Visualbuilder\EmailTemplates\Components;

use Filament\Forms\Components\Component;

class Iframe extends Component
{
    protected string $view = 'vb-email-templates::forms.components.iframe';

    public string $name;
    public string $src;
    public string $height;
    public string $width;

    public function __construct($name, $src = null, $height = '800px', $width = '100%')
    {
        $this->src = $src;
        $this->height = $height;
        $this->width = $width;
        $this->name = $name;
        $this->setUp();
    }

    protected function setUp(): void
    {
        parent::setUp();

    }

    public static function make($name, $src = null, $height = '800px', $width = '100%')
    {
        return new static($name, $src, $height, $width);
    }
}
