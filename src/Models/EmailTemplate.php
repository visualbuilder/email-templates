<?php

namespace Visualbuilder\EmailTemplates\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Visualbuilder\EmailTemplates\Database\Factories\EmailTemplateFactory;
use Visualbuilder\EmailTemplates\Facades\TokenHelper;

/**
 * @property int $id
 * @property string $key
 * @property array $from
 * @property string $name
 * @property string $view
 * @property array $cc
 * @property array $bcc
 * @property string $subject
 * @property string $title
 * @property string $preheader
 * @property string $language
 * @property string $content
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 */
class EmailTemplate extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;
    use SoftDeletes;

    /**
     * @var array
     */
    protected $fillable = [
        'from',
        'key',
        'name',
        'view',
        'subject',
        'title',
        'preheader',
        'content',
        'language',
        'logo',
        'cc',
        'bcc'

    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'deleted_at' => 'datetime:Y-m-d H:i:s',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
        'from' => 'array',
        'cc' => 'array',
        'bcc' => 'array',
    ];
    /**
     * @var string[]
     */
    protected $dates = ['deleted_at'];

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = ['theme'];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('screenshot')
            ->singleFile()
            ->acceptsMimeTypes(['image/png', 'image/jpeg', 'image/webp']);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->fit(Fit::Contain, 400, 600)
            ->nonQueued();
    }

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTableFromConfig();
        // Include the theme foreign key as a fillable attribute
        $this->fillable[] = config('filament-email-templates.theme_table_name') . '_id';

        // Include the tenant foreign key when multitenancy is enabled
        if (static::isMultitenancyEnabled()) {
            $this->fillable[] = static::getTenantForeignKeyName();
        }
    }

    /**
     * Remove temporary logo fields before mass assignment.
     */
    public function fill(array $attributes)
    {
        if (isset($attributes['logo_url'])) {
            if (($attributes['logo_type'] ?? null) === 'paste_url' && $attributes['logo_url']) {
                $attributes['logo'] = $attributes['logo_url'];
            }
            unset($attributes['logo_url'], $attributes['logo_type']);
        }

        return parent::fill($attributes);
    }

    protected static function boot()
    {
        parent::boot();

        if (static::isMultitenancyEnabled()) {
            $fk = static::getTenantForeignKeyName();
            $tenantModel = static::getTenantModelClass();
            $relationship = static::getOwnershipRelationshipName();

            // Register dynamic tenant relationship
            if ($tenantModel && $relationship) {
                static::resolveRelationUsing($relationship, function ($model) use ($tenantModel, $fk) {
                    return $model->belongsTo($tenantModel, $fk);
                });
            }

            // Auto-assign tenant on creation
            static::creating(function ($model) use ($fk) {
                if (is_null($model->$fk)) {
                    $tenant = static::resolveCurrentTenant();
                    if ($tenant) {
                        $model->$fk = $tenant->getKey();
                    }
                }
            });
        }

        // When an email template is updated
        static::updated(function ($template) {
            self::clearEmailTemplateCache($template->key, $template->language, static::getTenantIdFromModel($template));
        });

        // When an email template is deleted
        static::deleted(function ($template) {
            self::clearEmailTemplateCache($template->key, $template->language, static::getTenantIdFromModel($template));
        });
    }

    public function setTableFromConfig()
    {
        $this->table = config('filament-email-templates.table_name');
    }

    /**
     * Find an email template by key with optional tenant-aware fallback.
     *
     * When multitenancy is enabled, searches for a tenant-specific template first,
     * then falls back to the global template (null tenant_id).
     *
     * @param string $key
     * @param string|null $language
     * @param int|null $tenantId Explicit tenant ID, or null to auto-resolve from Filament context
     */
    public static function findEmailByKey($key, $language = null, $tenantId = null)
    {
        $language = $language ?? config('filament-email-templates.default_locale');
        $multitenancy = static::isMultitenancyEnabled();

        if ($multitenancy && $tenantId === null) {
            $tenant = static::resolveCurrentTenant();
            $tenantId = $tenant?->getKey();
        }

        $tenantPart = $multitenancy ? ($tenantId ?? 'global') : 'none';
        $cacheKey = "email_by_key_{$key}_{$language}_{$tenantPart}";

        $template = Cache::remember($cacheKey, now()->addMinutes(60), function () use ($key, $language, $tenantId, $multitenancy) {
            $query = self::query()
                ->where('key', $key)
                ->language($language);

            if ($multitenancy && $tenantId) {
                $fk = static::getTenantForeignKeyName();
                // Include both tenant-specific and global templates, prefer tenant-specific
                $query->where(function ($q) use ($fk, $tenantId) {
                    $q->where($fk, $tenantId)->orWhereNull($fk);
                })->orderByRaw("CASE WHEN {$fk} IS NOT NULL THEN 0 ELSE 1 END");
            }

            return $query->first();
        });

        // A lookup that fell back to another language is cached under the
        // REQUESTED language, which the template's own save hook cannot name.
        // Remember the language so clearEmailTemplateCache() can forget it.
        if ($template && $template->language !== $language) {
            static::rememberFallbackLanguage($key, $language);
        }

        return $template;
    }

    /** Languages that resolved to this key through the default-locale fallback. */
    protected static function fallbackLanguages(string $key): array
    {
        return (array) Cache::get("email_by_key_fallback_languages_{$key}", []);
    }

    protected static function rememberFallbackLanguage(string $key, string $language): void
    {
        $languages = static::fallbackLanguages($key);

        if (! in_array($language, $languages, true)) {
            $languages[] = $language;
            Cache::forever("email_by_key_fallback_languages_{$key}", $languages);
        }
    }

    /**
     * Clear all caches related to this email template.
     *
     * @param string $key The template key
     * @param string $language The template language
     * @param int|null $tenantId The tenant ID (null for global templates)
     * @return void
     */
    public static function clearEmailTemplateCache($key, $language, $tenantId = null)
    {
        $multitenancy = static::isMultitenancyEnabled();

        if ($multitenancy) {
            $tenantPart = $tenantId ?? 'global';
            Cache::forget("email_by_key_{$key}_{$language}_{$tenantPart}");
            // Also clear the global cache as the fallback chain may have changed
            Cache::forget("email_by_key_{$key}_{$language}_global");
        } else {
            Cache::forget("email_by_key_{$key}_{$language}_none");
        }

        // Lookups in other languages that fell back to this template were
        // cached under those languages; forget them too.
        foreach (static::fallbackLanguages($key) as $fallbackLanguage) {
            if ($multitenancy) {
                Cache::forget("email_by_key_{$key}_{$fallbackLanguage}_".($tenantId ?? 'global'));
                Cache::forget("email_by_key_{$key}_{$fallbackLanguage}_global");
            } else {
                Cache::forget("email_by_key_{$key}_{$fallbackLanguage}_none");
            }
        }
    }

    /**
     * Delete compiled view files for a specific email template.
     *
     * @param string $key The template key
     * @return void
     */
    protected static function deleteCompiledViewsForTemplate($key)
    {
        try {
            $viewPath = config('filament-email-templates.template_view_path', 'vb-email-templates::email');
            $compiledPath = storage_path('framework/views');

            if (!File::isDirectory($compiledPath)) {
                return;
            }

            $files = File::files($compiledPath);

            foreach ($files as $file) {
                $filePath = $file->getPathname();

                $contents = @file_get_contents($filePath);
                if ($contents === false) {
                    continue;
                }

                if (
                    str_contains($contents, $viewPath) ||
                    str_contains($contents, 'vb-email-templates') ||
                    str_contains($contents, $key)
                ) {
                    @unlink($filePath);
                }
            }
        } catch (\Exception $e) {
            Log::warning(
                'Failed to delete compiled views for email template',
                [
                    'key' => $key,
                    'error' => $e->getMessage()
                ]
            );
        }
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public static function getSendToSelectOptions()
    {
        return collect(config('emailTemplate.recipients'));
    }

    /**
     * @return EmailTemplateFactory
     */
    protected static function newFactory()
    {
        return EmailTemplateFactory::new();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->name ?? class_basename($this);
    }

    /**
     * Get the assigned theme or the default
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function theme()
    {
        return $this->belongsTo(EmailTemplateTheme::class, config('filament-email-templates.theme_table_name') . '_id')->withDefault(function ($model) {
            return EmailTemplateTheme::where('is_default', true)->first();
        });
    }

    /**
     * Gets base64 encoded content - to add to an iframe
     *
     * @return string
     */
    public function getBase64EmailPreviewData()
    {
        $data = $this->getEmailPreviewData();
        $content = view($this->view_path, ['data' => $data])->render();

        return base64_encode($content);
    }

    /**
     * @return array
     */
    public function getEmailPreviewData()
    {
        $models = self::createEmailPreviewData();

        $previewOverrides = config('filament-email-templates.preview_data', []);

        $applyOverrides = function (string $content) use ($previewOverrides): string {
            foreach ($previewOverrides as $tokenPath => $value) {
                $content = str_replace("##{$tokenPath}##", (string) $value, $content);
            }

            return $content;
        };

        return [
            'user' => $models->user ?? null,
            'content' => TokenHelper::replace($applyOverrides($this->content ?? ''), $models),
            'subject' => TokenHelper::replace($applyOverrides($this->subject ?? ''), $models),
            'preHeaderText' => TokenHelper::replace($applyOverrides($this->preheader ?? ''), $models),
            'title' => TokenHelper::replace($applyOverrides($this->title ?? ''), $models),
            'theme' => $this->theme->colours,
            'logo' => $this->logo,
        ];
    }

    /**
     * @return object
     */
    public static function createEmailPreviewData()
    {
        $models = (object)[];

        $userModel = config('filament-email-templates.recipients')[0] ?? null;
        if ($userModel) {
            $models->user = $userModel::first();
        }
        $models->tokenUrl = URL::to('/');
        $models->verificationUrl = URL::to('/');
        $models->expiresAt = now()->addDays(7)->format('d/m/Y H:i');
        $models->plainText = Str::random(32);

        foreach (config('filament-email-templates.preview_models', []) as $prefix => $modelClass) {
            if (class_exists($modelClass)) {
                $record = $modelClass::first();
                if ($record) {
                    $models->{$prefix} = $record;
                }
            }
        }

        return $models;
    }

    /**
     * Efficient method to return requested template locale or default language template in one query
     *
     * @param Builder $query
     * @param $language
     *
     * @return Builder
     */
    public function scopeLanguage(Builder $query, $language)
    {
        $languages = [$language, config('filament-email-templates.default_locale')];

        return $query->whereIn('language', $languages)
            ->orderByRaw(
                "(CASE WHEN language = ? THEN 1 ELSE 2 END)",
                [$language]
            );
    }

    /**
     * @return Attribute
     */
    public function viewPath(): Attribute
    {
        return new Attribute(
            get: fn () => config('filament-email-templates.template_view_path') . '.' . $this->view
        );
    }

    /**
     * @return bool
     */
    public function getMailableExistsAttribute(): bool
    {
        $className = Str::studly($this->key);
        $filePath = app_path(config('filament-email-templates.mailable_directory') . "/{$className}.php");

        return File::exists($filePath);
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getMailableClass()
    {
        $className = Str::studly($this->key);
        $directory = str_replace('/', '\\', config('filament-email-templates.mailable_directory', 'Mail/Visualbuilder/EmailTemplates'));
        $fullClassName = "App\\" . rtrim($directory, '\\') . "\\{$className}";

        if (!class_exists($fullClassName)) {
            throw new \Exception("Mailable class {$fullClassName} does not exist.");
        }

        return $fullClassName;
    }


    public function getLogoAttribute(): string
    {
        $logo = $this->attributes['logo'] ?? config('filament-email-templates.logo');

        return Str::isUrl($logo) ? $logo : asset($logo);
    }

    // ── Multitenancy helpers (config-driven, no Plugin dependency) ──

    public static function isMultitenancyEnabled(): bool
    {
        return (bool) config('filament-email-templates.multitenancy.enabled', false);
    }

    public static function getTenantForeignKeyName(): string
    {
        if ($key = config('filament-email-templates.multitenancy.tenant_foreign_key')) {
            return $key;
        }

        $model = config('filament-email-templates.multitenancy.tenant_model');

        return $model ? Str::snake(class_basename($model)) . '_id' : 'tenant_id';
    }

    public static function getTenantModelClass(): ?string
    {
        return config('filament-email-templates.multitenancy.tenant_model');
    }

    public static function getOwnershipRelationshipName(): string
    {
        if ($rel = config('filament-email-templates.multitenancy.ownership_relationship')) {
            return $rel;
        }

        $model = config('filament-email-templates.multitenancy.tenant_model');

        return $model ? Str::camel(class_basename($model)) : 'tenant';
    }

    /**
     * Check if a template is a global (system) template.
     */
    public function isGlobal(): bool
    {
        if (! static::isMultitenancyEnabled()) {
            return true;
        }

        $fk = static::getTenantForeignKeyName();

        return is_null($this->$fk);
    }

    protected static function resolveCurrentTenant(): ?Model
    {
        try {
            if (class_exists(\Filament\Facades\Filament::class)) {
                return \Filament\Facades\Filament::getTenant();
            }
        } catch (\Throwable) {
            // Not in a Filament context (e.g. queue worker, artisan command)
        }

        return null;
    }

    protected static function getTenantIdFromModel(self $template): ?int
    {
        if (! static::isMultitenancyEnabled()) {
            return null;
        }

        $fk = static::getTenantForeignKeyName();

        return $template->$fk;
    }
}
