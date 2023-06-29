<?php

namespace Visualbuilder\EmailTemplates\Helpers;

use Visualbuilder\EmailTemplates\Contracts\TokenHelperInterface;

class TokenHelper implements TokenHelperInterface
{
    public function replaceTokens($string, $model)
    {
        return $string;
    }
}