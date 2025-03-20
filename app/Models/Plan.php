<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Plan extends Model {
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'billing_period',
        'features',
        'is_active'
    ];

    protected $casts = [
        'features' => 'array',
        'is_active' => 'boolean',
        'price' => 'decimal:2'
    ];

    public function subscriptions() {
        return $this->hasMany(Subscription::class);
    }
}
