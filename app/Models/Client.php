<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model {
    use SoftDeletes;

    protected $fillable = [
        'name',
        'domain',
        'database_name',
        'database_user',
        'database_password',
        'admin_email',
        'admin_password',
        'is_active'
    ];

    protected $hidden = [
        'database_password',
        'admin_password'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];
}