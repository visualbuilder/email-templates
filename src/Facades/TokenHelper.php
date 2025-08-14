<?php

namespace Visualbuilder\EmailTemplates\Facades;

use Visualbuilder\EmailTemplates\Contracts\TokenReplacementInterface;
use Illuminate\Support\Facades\Facade;

class TokenHelper extends Facade
{
    protected static function getFacadeAccessor()
    {
        return TokenReplacementInterface::class;
    }

    public static function replace(string $content, $models): string
    {
        return static::resolveFacadeInstance(static::getFacadeAccessor())->replaceTokens($content, $models);
    }
}
