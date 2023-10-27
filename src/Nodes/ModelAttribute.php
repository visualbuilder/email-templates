<?php

namespace Visualbuilder\EmailTemplates\Nodes;

use Tiptap\Core\Node;
use Tiptap\Utils\HTML;

class ModelAttribute extends Node
{
    public static $name = 'model-attribute';

    public static $priority = 100;

    public function addOptions()
    {
        return [
                'HTMLAttributes' => [],
        ];
    }

    public function parseHTML()
    {
        return [
                [
                        'tag' => 'model-attribute[data-path]',
                ],

        ];
    }

    public function renderHTML($node,$HTMLAttributes = [])
    {
        return ['model-attribute',   HTML::mergeAttributes($this->options['HTMLAttributes'], $HTMLAttributes), 0];
    }
}