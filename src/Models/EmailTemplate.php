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
use Illuminate\Support\Facades\Artisan;
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

        // When an email template is updated
        static::updated(function ($template) {
            self::clearEmailTemplateCache($template->key, $template->language);
        });

        // When an email template is deleted
        static::deleted(function ($template) {
            self::clearEmailTemplateCache($template->key, $template->language);
        });
    }

    public function setTableFromConfig()
    {
        $this->table = config('filament-email-templates.table_name');
    }

    public static function findEmailByKey($key, $language = null)
    {
        $cacheKey = "email_by_key_{$key}_{$language}";

        //For multi site domains this key will need to include the site_id
        return Cache::remember($cacheKey, now()->addMinutes(60), function () use ($key, $language) {
            return self::query()
                ->language($language ?? config('filament-email-templates.default_locale'))
                ->where("key", $key)
                ->firstOrFail();
        });
    }

    /**
     * Clear all caches related to this email template.
     *
     * This method ensures that when a template is updated, the changes are
     * immediately visible to users by clearing:
     * - Redis/cache driver cache for the template model
     * - Compiled Blade view files
     * - OPcache (PHP bytecode cache)
     *
     * @param string $key The template key
     * @param string $language The template language
     * @return void
     */
    public static function clearEmailTemplateCache($key, $language)
    {
        $cacheKey = "email_by_key_{$key}_{$language}";

        // Clear the actual cached template model
        Cache::forget($cacheKey);

        Artisan::call('optimize:clear');

        // Clear OPcache if available
        if (function_exists('opcache_reset')) {
            opcache_reset();
            Log::info("OPcache cleared");
        }
    }

    /**
     * Delete compiled view files for a specific email template.
     *
     * This method physically removes the compiled PHP view files from the
     * storage/framework/views directory. This is more aggressive than view:clear
     * and ensures that Blade will recompile the views on the next request.
     *
     * @param string $key The template key
     * @return void
     */
    protected static function deleteCompiledViewsForTemplate($key)
    {
        try {
            $viewPath = config('filament-email-templates.template_view_path', 'vb-email-templates::email');
            $compiledPath = storage_path('framework/views');

            // If the compiled views directory doesn't exist, nothing to delete
            if (!File::isDirectory($compiledPath)) {
                return;
            }

            // Get all compiled view files
            $files = File::files($compiledPath);

            // Delete compiled files that might contain this template's content
            // Compiled view filenames are MD5 hashes, so we can't match them exactly
            // Instead, we look for files that contain the template's view path or key
            foreach ($files as $file) {
                $filePath = $file->getPathname();

                // Read the file and check if it contains references to our template
                // This is a heuristic approach since compiled views include the original path
                $contents = @file_get_contents($filePath);
                if ($contents === false) {
                    continue;
                }

                // Check if this compiled view references our email template views
                if (
                    str_contains($contents, $viewPath) ||
                    str_contains($contents, 'vb-email-templates') ||
                    str_contains($contents, $key)
                ) {
                    @unlink($filePath);
                }
            }
        } catch (\Exception $e) {
            // If deletion fails, log but don't throw
            // The view:clear command should have already cleared the cache
            \Illuminate\Support\Facades\Log::warning(
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
        /**
         * Iframes normally use src attribute to load content from a url
         * This means an extra http request
         *  Below method includes the content directly as base64 encoded
         */

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

        // Apply static overrides: replace ##prefix.attr## before TokenHelper runs
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
        //Setup some data for previewing email template
        if ($userModel) {
            $models->user = $userModel::first();
        }
        $models->tokenUrl = URL::to('/');
        $models->verificationUrl = URL::to('/');
        $models->expiresAt = now()->addDays(7)->format('d/m/Y H:i');
        /* Not used in preview but need to add something */
        $models->plainText = Str::random(32);

        // Load registered preview models (first record of each)
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
        //Get Database logo or config logo
        $logo = $this->attributes['logo'] ?? config('filament-email-templates.logo');

        // Return the logo if it's a full URL, otherwise, return the asset URL.
        return Str::isUrl($logo) ? $logo : asset($logo);
    }

}
