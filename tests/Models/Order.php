<?php

namespace Visualbuilder\EmailTemplates\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Visualbuilder\EmailTemplates\Contracts\HasTokenAttributes;

/**
 * Fixture for TokenRegistry attribute derivation: fillable + appends +
 * declared tokenAttributes() should be enumerated, hidden attributes and
 * denylisted secrets must never appear.
 */
class Order extends Model implements HasTokenAttributes
{
    protected $fillable = ['reference', 'total', 'secret_note'];

    protected $hidden = ['secret_note'];

    protected $appends = ['summary'];

    public function getSummaryAttribute(): string
    {
        return "{$this->reference}: {$this->total}";
    }

    public function getContactNameAttribute(): string
    {
        return 'Delegated Contact';
    }

    public function tokenAttributes(): array
    {
        return ['contact_name', 'secret_note', 'api_token'];
    }
}
