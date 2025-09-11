<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetRegisterRow extends Model
{
    protected $fillable = ['client_id', 'register_id', 'source_filename', 'data'];
    protected $casts = ['data' => 'array'];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
    public function register()
    {
        return $this->belongsTo(AssetRegister::class, 'register_id');
    }
}
