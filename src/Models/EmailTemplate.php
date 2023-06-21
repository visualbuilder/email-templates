<?php

namespace Visualbuilder\EmailTemplates\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Visualbuilder\EmailTemplates\Database\Factories\EmailTemplateFactory;
use Visualbuilder\Core\Models\User;

/**
 * @property integer $id
 * @property string $key
 * @property string $from
 * @property string $name
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
	use HasFactory, SoftDeletes;

	/**
	 * @var array
	 */
	protected $fillable = ['from', 'key', 'name', 'subject', 'title', 'preheader', 'content', 'language', 'send_to'];
	protected $casts    = [
        'deleted_at' => 'datetime:Y-m-d H:i:s',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];
    protected $table = 'email_templates';
    
    protected $dates = ['deleted_at'];

	public function __toString()
	{
		return $this->name??class_basename($this);
	}

    protected static function newFactory() {
        return EmailTemplateFactory::new();
    }
    
    
    public static function findEmailByKey($key, $language=null)
    {
        
        return self::query()
            ->language($language??config('email-templates.default-locale'))
            ->where("key", $key)
            ->firstOrFail();
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
		$languages = [$language, config('email-templates.default-locale')];
		return $query->whereIn('language', $languages)
		             ->orderByRaw('field(language, ?, ?)', $languages);
	}



    /**
     * Requires predefined set of locales to be listed in a config
     * This may be a separate package??
     * Disabling for now until we decide best way to implement
     */
    /**
	public function missingTranslations()
	{
		$languagesForThisKey = self::newQuery()
		                           ->select('language')
		                           ->where('key', $this->key)
		                           ->pluck('language')
		                           ->toArray();

		return array_diff(array_keys(config('vb-languages')), $languagesForThisKey);
	}

	public function languageFormatted(): Attribute
	{
		$languages = config('vb-languages');

		return new Attribute(
			get: fn($value) => array_key_exists($this->language, $languages) ? '<span class="me-2 flag-icon flag-icon-'.
			                                                                   $languages[ $this->language ][ 'flag-icon' ].'"></span>'.
			                                                                   $languages[ $this->language ][ 'display' ] : '',
		);
	}
    **/
    
	public static function getSendToSelectOptions()
	{
		return collect(config('emailTemplate.recipients'));
	}

	public static function getEmailPreviewData()
	{
		$model = (object) [];
        //Setup some data for previewing email template
		$model->user            = User::first();
		$model->tokens          = (object) ['tokenURL' => URL::to('/')];
		$model->verificationUrl = URL::to('/');
		$model->token           = (object) ['expiresAt' => now()];
		$model->plainText       = Str::random(32);
		return $model;
	}
}
