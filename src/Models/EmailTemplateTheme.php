<?php

namespace Visualbuilder\EmailTemplates\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Visualbuilder\EmailTemplates\Database\Factories\EmailTemplateThemeFactory;

class EmailTemplateTheme extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'colours',
        'is_default',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'colours' => 'array',
        'deleted_at' => 'datetime:Y-m-d H:i:s',
    ];

    protected $dates = ['deleted_at'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTableFromConfig();

        if (EmailTemplate::isMultitenancyEnabled()) {
            $this->fillable[] = EmailTemplate::getTenantForeignKeyName();
        }
    }

    protected static function boot()
    {
        parent::boot();

        if (EmailTemplate::isMultitenancyEnabled()) {
            $fk = EmailTemplate::getTenantForeignKeyName();
            $tenantModel = EmailTemplate::getTenantModelClass();
            $relationship = EmailTemplate::getOwnershipRelationshipName();

            if ($tenantModel && $relationship) {
                static::resolveRelationUsing($relationship, function ($model) use ($tenantModel, $fk) {
                    return $model->belongsTo($tenantModel, $fk);
                });
            }

            static::creating(function ($model) use ($fk) {
                if (is_null($model->$fk)) {
                    try {
                        $tenant = class_exists(\Filament\Facades\Filament::class)
                            ? \Filament\Facades\Filament::getTenant()
                            : null;
                    } catch (\Throwable) {
                        $tenant = null;
                    }

                    if ($tenant) {
                        $model->$fk = $tenant->getKey();
                    }
                }
            });
        }
    }

    public function setTableFromConfig()
    {
        $this->table = config('filament-email-templates.theme_table_name');
    }

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

    /**
     * Check if this theme is a global (system) theme.
     */
    public function isGlobal(): bool
    {
        if (! EmailTemplate::isMultitenancyEnabled()) {
            return true;
        }

        $fk = EmailTemplate::getTenantForeignKeyName();

        return is_null($this->$fk);
    }

    protected static function newFactory()
    {
        return EmailTemplateThemeFactory::new();
    }
}
