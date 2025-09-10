<?php

namespace App\Models;

use App\Models\Auditor;
use App\Models\Client;
use Illuminate\Database\Eloquent\Model;

class AssetRegister extends Model
{
    protected $fillable = [
    'client_id',
    'headings',
    'source_filename',
    'audit_id',
];
 // This is the key line:
    protected $casts = [
        'headings' => 'array',   // Eloquent will JSON-encode on save, decode on read
    ];
public function client()  { return $this->belongsTo(Client::class); }
    public function auditor() { return $this->belongsTo(Auditor::class, 'audit_id'); }

}
