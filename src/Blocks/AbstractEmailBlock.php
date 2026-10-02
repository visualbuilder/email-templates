<?php

declare(strict_types=1);

namespace Visualbuilder\EmailTemplates\Blocks;

use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Visualbuilder\EmailTemplates\Blocks\Contracts\EmailBlockDefinition;
use Visualbuilder\EmailTemplates\Enums\BlockRenderMode;
use Visualbuilder\EmailTemplates\Facades\TokenHelper;
use Visualbuilder\EmailTemplates\TokenRegistry;
use Visualbuilder\FilamentTinyEditor\TinyEditor;

/**
 * Base for block types: token replacement on tokenFields(), public image
 * URLs for imageFields(), and rendering through view($mode).
 */
abstract class AbstractEmailBlock implements EmailBlockDefinition
{
    /** A link field accepts an http(s) URL or a single token such as ##endUser.magic_link##. */
    public const URL_PATTERN = '/^(https?:\/\/\S+|##[A-Za-z0-9_.]+##)$/';

    /**
     * Data keys whose string values go through token replacement.
     *
     * @return array<int, string>
     */
    protected function tokenFields(): array
    {
        return [];
    }

    /**
     * Data keys holding a stored upload path, replaced by a public URL.
     *
     * @return array<int, string>
     */
    protected function imageFields(): array
    {
        return [];
    }

    /**
     * Public URL for a stored block image. An image picked but not saved yet
     * (a Livewire temporary upload) returns null, so a preview leaves it out
     * until the record is saved.
     */
    public static function imageUrl(mixed $value): ?string
    {
        if (is_array($value)) {
            $value = Arr::first($value);
        }

        if ($value instanceof TemporaryUploadedFile || blank($value) || ! is_string($value)) {
            return null;
        }

        return Str::isUrl($value) ? $value : Storage::disk(static::imageDisk())->url($value);
    }

    /**
     * Images must be served from a public URL that mail clients can fetch;
     * asset() would point at the (possibly protected) admin domain.
     */
    public static function imageDisk(): string
    {
        return config('filament-email-templates.block_images_disk')
            ?: (config('filament.default_filesystem_disk') ?: 'public');
    }

    public function view(BlockRenderMode $mode): ?string
    {
        return match ($mode) {
            BlockRenderMode::Email => 'vb-email-templates::email.blocks.'.$this->name(),
        };
    }

    public function summary(array $data): ?string
    {
        return null;
    }

    public function nestable(): bool
    {
        return true;
    }

    public function render(array $data, BlockRenderContext $context): string
    {
        $view = $this->view($context->mode);

        if ($view === null) {
            return '';
        }

        return view($view, [
            'block' => $this->prepare($data, $context),
            'theme' => $context->theme,
        ])->render();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function prepare(array $data, BlockRenderContext $context): array
    {
        if ($context->models !== null) {
            foreach ($this->tokenFields() as $key) {
                if (is_string($data[$key] ?? null)) {
                    $data[$key] = TokenHelper::replace($data[$key], $context->models);
                }
            }
        }

        foreach ($this->imageFields() as $key) {
            $data[$key] = static::imageUrl($data[$key] ?? null);
        }

        return $data;
    }

    /**
     * First $limit characters of the text in an HTML fragment, or null when empty.
     */
    protected static function textSummary(mixed $html, int $limit = 50): ?string
    {
        $text = trim(html_entity_decode(strip_tags(is_string($html) ? $html : ''), ENT_QUOTES | ENT_HTML5));

        return $text === '' ? null : Str::substr($text, 0, $limit);
    }

    /** Image upload stored with public visibility on the block image disk. */
    protected static function imageUpload(string $name): FileUpload
    {
        return FileUpload::make($name)
            ->image()
            ->disk(static::imageDisk())
            ->visibility('public')
            ->directory(config('filament-email-templates.block_images'))
            ->maxSize(2048);
    }

    /** Rich text editor configured like the template body editor. */
    protected static function richEditor(string $name): TinyEditor
    {
        return TinyEditor::make($name)
            ->profile('email-template')
            ->setCustomConfigs(fn () => ['vbtokens_list' => app(TokenRegistry::class)->menu()]);
    }
}
