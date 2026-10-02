<?php

declare(strict_types=1);

namespace Visualbuilder\EmailTemplates\Blocks\Types;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Support\Icons\Heroicon;
use Visualbuilder\EmailTemplates\Blocks\AbstractEmailBlock;
use Visualbuilder\EmailTemplates\Blocks\BlockRenderContext;

final class DividerBlock extends AbstractEmailBlock
{
    public const SPACINGS = ['small' => 'Small', 'medium' => 'Medium', 'large' => 'Large'];

    public function name(): string
    {
        return 'divider';
    }

    public function label(): string
    {
        return 'Divider / spacer';
    }

    public function icon(): Heroicon
    {
        return Heroicon::OutlinedMinus;
    }

    public function schema(): array
    {
        return [
            Select::make('spacing')
                ->label('Spacing')
                ->options(self::SPACINGS)
                ->default('medium')
                ->required(),
            Toggle::make('show_line')
                ->label('Show a line')
                ->default(true),
        ];
    }

    public function summary(array $data): ?string
    {
        return self::SPACINGS[$data['spacing'] ?? ''] ?? null;
    }

    protected function prepare(array $data, BlockRenderContext $context): array
    {
        $data = parent::prepare($data, $context);
        $data['line_color'] = self::lineColour($context->theme);

        return $data;
    }

    /**
     * body_color at 20% over content_bg_color, as a solid hex: email clients
     * do not reliably apply opacity to borders. #e5e7eb when either colour is
     * missing or not a hex value.
     *
     * @param  array<string, mixed>  $theme
     */
    public static function lineColour(array $theme): string
    {
        $foreground = self::rgb($theme['body_color'] ?? null);
        $background = self::rgb($theme['content_bg_color'] ?? null);

        if ($foreground === null || $background === null) {
            return '#e5e7eb';
        }

        $mixed = array_map(
            fn (int $fg, int $bg): int => (int) round($fg * 0.2 + $bg * 0.8),
            $foreground,
            $background,
        );

        return vsprintf('#%02x%02x%02x', $mixed);
    }

    /** @return array{0: int, 1: int, 2: int}|null */
    private static function rgb(mixed $hex): ?array
    {
        if (! is_string($hex) || ! preg_match('/^#?([0-9a-f]{3}|[0-9a-f]{6})$/i', trim($hex), $matches)) {
            return null;
        }

        $digits = strlen($matches[1]) === 3
            ? implode('', array_map(fn (string $c): string => $c.$c, str_split($matches[1])))
            : $matches[1];

        return [hexdec(substr($digits, 0, 2)), hexdec(substr($digits, 2, 2)), hexdec(substr($digits, 4, 2))];
    }
}
