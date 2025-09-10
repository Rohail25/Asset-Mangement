<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetAuditRow extends Model
{
    protected $fillable = ['client_id', 'file_id', 'auditor_id', 'data'];

    protected $casts = [
        'data' => 'array'
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
    public function file()
    {
        return $this->belongsTo(AssetAuditFile::class, 'file_id');
    }
    public function auditor()
    {
        return $this->belongsTo(Auditor::class);
    }
}
