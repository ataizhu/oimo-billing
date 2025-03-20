<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class Payment extends Model {
    use HasFactory;

    protected $fillable = [
        'user_id',
        'invoice_id',
        'amount',
        'status',
        'payment_method',
        'transaction_id',
        'metadata'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'metadata' => 'array'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function invoice() {
        return $this->belongsTo(Invoice::class);
    }

    public function isSuccessful() {
        return $this->status === 'completed';
    }

    public function isFailed() {
        return $this->status === 'failed';
    }

    public function isPending() {
        return $this->status === 'pending';
    }
}
