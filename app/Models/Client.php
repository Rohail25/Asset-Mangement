<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = [
        'name',
        'email',
        'contact',
        'company_name',
        'audit_start_date',
        'due_date',
        'audit_status',
        'auditor_id',
        'role'
    ];
    public function auditor()
    {
        return $this->belongsTo(Auditor::class);
    }
}
