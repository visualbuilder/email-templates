<?php

namespace Visualbuilder\EmailTemplates\Models;

use Visualbuilder\EmailTemplates\Database\Factories\EmailTemplateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Visualbuilder\EmailTemplates\Database\Factories\EmailTemplateThemeFactory;

class EmailTemplateTheme extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'colours',
        'is_active',
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
    }

    public function setTableFromConfig()
    {
        $this->table = config('email-templates.theme_table_name');
    }

    protected static function newFactory()
    {
        return EmailTemplateThemeFactory::new();
    }
}
