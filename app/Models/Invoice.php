<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;
use Illuminate\Support\Facades\Date;

class Invoice extends Model {
    use HasFactory;

    protected $fillable = [
        'user_id',
        'subscription_id',
        'number',
        'amount',
        'status',
        'due_date',
        'paid_at',
        'items'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'due_date' => 'datetime',
        'paid_at' => 'datetime',
        'items' => 'array'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function subscription() {
        return $this->belongsTo(Subscription::class);
    }

    public function payments() {
        return $this->hasMany(Payment::class);
    }

    public function isPaid() {
        return $this->status === 'paid' && $this->paid_at !== null;
    }

    public function isOverdue() {
        return !$this->isPaid() && $this->due_date->isPast();
    }

    public function markAsPaid() {
        $this->update([
            'status' => 'paid',
            'paid_at' => Date::now(),
        ]);
    }
}
