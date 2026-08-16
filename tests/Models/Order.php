<?php

namespace Visualbuilder\EmailTemplates\Tests\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Fixture for TokenRegistry attribute derivation: fillable + appends
 * should be enumerated, hidden attributes must never appear.
 */
class Order extends Model
{
    protected $fillable = ['reference', 'total', 'secret_note'];

    protected $hidden = ['secret_note'];

    protected $appends = ['summary'];

    public function getSummaryAttribute(): string
    {
        return "{$this->reference}: {$this->total}";
    }
}
