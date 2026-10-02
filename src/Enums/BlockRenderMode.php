<?php

declare(strict_types=1);

namespace Visualbuilder\EmailTemplates\Enums;

/**
 * Where a block is being rendered. Email is the only target today; a Web
 * case for pages can be added without changing the block contract.
 */
enum BlockRenderMode: string
{
    case Email = 'email';
}
