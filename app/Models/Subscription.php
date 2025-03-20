<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class Subscription extends Model {
    use HasFactory;

    protected $fillable = [
        'user_id',
        'plan_id',
        'status',
        'trial_ends_at',
        'starts_at',
        'ends_at',
        'canceled_at',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'canceled_at' => 'datetime',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function plan() {
        return $this->belongsTo(Plan::class);
    }

    public function invoices() {
        return $this->hasMany(Invoice::class);
    }

    public function isActive() {
        return $this->status === 'active' &&
            ($this->ends_at === null || $this->ends_at->isFuture());
    }

    public function isOnTrial() {
        return $this->trial_ends_at !== null &&
            $this->trial_ends_at->isFuture();
    }

    public function isCanceled() {
        return $this->canceled_at !== null;
    }

    public function hasEnded() {
        return $this->ends_at !== null &&
            $this->ends_at->isPast();
    }
}
