<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Auditor extends Authenticatable
{
    protected $fillable = [
        'name',
        'email',
        'username',
        'password',
        'role',
        'status'
    ];
    protected $hidden = ['password'];
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }
}
