<?php

declare(strict_types=1);

namespace Visualbuilder\EmailTemplates\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Visualbuilder\EmailTemplates\Layout\EmailLayoutRenderer;

/**
 * A reusable group of email blocks from the block library, inserted into a
 * template layout through a "Saved block" item.
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property array|null $layout
 * @property bool $is_active
 */
class EmailBlock extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'description', 'layout', 'is_active'];

    protected $casts = [
        'layout' => 'array',
        'is_active' => 'boolean',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('filament-email-templates.blocks_table_name', 'vb_email_blocks');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Base64 HTML of this block rendered inside the default theme, for the
     * preview iframe.
     */
    public function getBase64EmailPreviewData(): string
    {
        $theme = EmailTemplateTheme::query()->where('is_default', true)->firstOrFail();
        $models = EmailTemplate::createEmailPreviewData();
        $data = [
            'title' => $this->name,
            'preHeaderText' => '',
            'logo' => (new EmailTemplate)->logo,
            'theme' => $theme->colours,
            'colours' => $theme->colours,
            'blocks' => app(EmailLayoutRenderer::class)->withTheme($theme->colours)->render($this->layout ?? [], $models),
        ];

        return base64_encode(view(config('filament-email-templates.template_view_path').'.layout_preview', ['data' => $data])->render());
    }
}
