<?php

namespace Visualbuilder\EmailTemplates\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Visualbuilder\EmailTemplates\Database\Factories\EmailTemplateFactory;

/**
 * @property int $id
 * @property string $key
 * @property string $from
 * @property string $name
 * @property string $view
 * @property string $send_to
 * @property object $cc
 * @property object $bcc
 * @property string $subject
 * @property string $title
 * @property string $preheader
 * @property string $language
 * @property string $content
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 */
class EmailTemplate extends Model
{
    use HasFactory;
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
        'send_to',
    ];
    protected $casts = [
        'deleted_at' => 'datetime:Y-m-d H:i:s',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];
    protected $dates = ['deleted_at'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTableFromConfig();
    }

    public function setTableFromConfig()
    {
        $this->table = config('email-templates.table_name');
    }

    protected static function newFactory()
    {
        return EmailTemplateFactory::new();
    }

    public function __toString()
    {
        return $this->name ?? class_basename($this);
    }

    public static function findEmailByKey($key, $language = null)
    {
        return self::query()
            ->language($language ?? config('email-templates.default_locale'))
            ->where("key", $key)
            ->firstOrFail();
    }

    public static function getSendToSelectOptions()
    {
        return collect(config('emailTemplate.recipients'));
    }

    public static function getEmailPreviewData()
    {
        $model = (object) [];
        $userModel = config('email-templates.recipients')[0];
        //Setup some data for previewing email template
        $model->user = $userModel::first();
        $model->tokenUrl = URL::to('/');
        $model->verificationUrl = URL::to('/');
        $model->expiresAt = now();
        $model->plainText = Str::random(32);

        return $model;
    }

    /**
     * Efficient method to return requested template locale or default language template in one query
     *
     * @param  Builder  $query
     * @param $language
     *
     * @return Builder
     */
    public function scopeLanguage(Builder $query, $language)
    {
        $languages = [$language, config('email-templates.default_locale')];

        return $query->whereIn('language', $languages)
            ->orderBy('language');
        //  ->orderByRaw('field(language, ?, ?)', $languages); // order by field is not present in sqlite
    }

    /**
     * @return Attribute
     */
    public function viewPath(): Attribute
    {
        return new Attribute(
            get: fn () => config('email-templates.template_view_path').'.'.$this->view
        );
    }

    /**
     * @return string
     */
    public function previewUrl(): string
    {
        return route('email-template.preview', $this);
    }

    /**
     * @return bool
     */
    public function getMailableExistsAttribute(): bool
    {
        $className = Str::studly($this->key);
        $filePath = app_path(config('email-templates.mailable_directory')."/{$className}.php");

        return File::exists($filePath);
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getMailableClass()
    {
        $className = Str::studly($this->key);
        $directory = str_replace('/', '\\', config('email-templates.mailable_directory', 'Mail/Visualbuilder/EmailTemplates')); // Convert slashes to namespace format
        $fullClassName = "\\App\\{$directory}\\{$className}";

        if (! class_exists($fullClassName)) {
            throw new \Exception("Mailable class {$fullClassName} does not exist.");
        }

        return $fullClassName;
    }
}
