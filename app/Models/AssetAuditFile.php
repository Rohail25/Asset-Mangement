<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssetAuditFile extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'client_id',
        'auditor_id',
        'type',
        'label',
        'source_filename',
        'rows_count'
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
    public function auditor()
    {
        return $this->belongsTo(Auditor::class);
    }
    public function rows()
    {
        return $this->hasMany(AssetAuditRow::class, 'file_id');
    }
}
