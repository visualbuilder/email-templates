<?php

namespace Visualbuilder\EmailTemplates\Contracts;

interface CreateMailableInterface
{
    public function createMailable($record);
}
