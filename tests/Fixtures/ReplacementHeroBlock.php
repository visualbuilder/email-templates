<?php

namespace Visualbuilder\EmailTemplates\Tests\Fixtures;

use Filament\Support\Icons\Heroicon;
use Visualbuilder\EmailTemplates\Blocks\AbstractEmailBlock;

/** Test-only block that takes over the package's 'hero' name. */
class ReplacementHeroBlock extends AbstractEmailBlock
{
    public function name(): string
    {
        return 'hero';
    }

    public function label(): string
    {
        return 'Host hero';
    }

    public function icon(): Heroicon
    {
        return Heroicon::OutlinedPhoto;
    }

    public function schema(): array
    {
        return [];
    }
}
