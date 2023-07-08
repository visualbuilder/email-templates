<?php

namespace Visualbuilder\EmailTemplates\Contracts;

interface TokenHelperInterface
{
    public function replaceTokens($string, $model);
}
