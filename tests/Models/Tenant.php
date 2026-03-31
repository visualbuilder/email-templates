<?php

namespace Visualbuilder\EmailTemplates\Tests\Models;

use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    protected $fillable = ['name', 'slug'];
}
