<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditFieldOption extends Model
{
    protected $fillable = ['field_id', 'value', 'order_index'];

    public function field()
    {
        return $this->belongsTo(AssetAuditField::class, 'field_id');
    }
}
