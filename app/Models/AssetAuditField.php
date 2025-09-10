<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssetAuditField extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'client_id','name','label','description','type',
        'required','scan_enabled','order_index','auditor_id'
    ];

    public function client()   { return $this->belongsTo(Client::class); }
    public function options()  { return $this->hasMany(AuditFieldOption::class, 'field_id')->orderBy('order_index'); }
}
