<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Loan extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_PAID = 'paid';

    protected $fillable = [
        'user_id',
        'borrower_id',
        'principal_amount',
        'outstanding_principal',
        'interest_rate',
        'outstanding_interest',
        'loan_date',
        'commitment_date',
        'agreed_interest_amount',
        'commitment_broken_at',
        'next_interest_due_date',
        'status',
    ];

    protected $casts = [
        'principal_amount' => 'decimal:2',
        'outstanding_principal' => 'decimal:2',
        'interest_rate' => 'decimal:2',
        'outstanding_interest' => 'decimal:2',
        'agreed_interest_amount' => 'decimal:2',
        'loan_date' => 'date',
        'commitment_date' => 'date',
        'commitment_broken_at' => 'datetime',
        'next_interest_due_date' => 'date',
    ];

    public function payments(): HasMany
    {
        return $this->hasMany(LoanPayment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function borrower(): BelongsTo
    {
        return $this->belongsTo(Borrower::class);
    }

    public function notificationLogs(): HasMany
    {
        return $this->hasMany(WhatsappNotificationLog::class);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function hasCommitmentDate(): bool
    {
        return $this->commitment_date !== null;
    }

    public function hasBrokenCommitment(): bool
    {
        return $this->commitment_broken_at !== null;
    }

    public function isOverdue(): bool
    {
        return $this->isActive() && $this->next_interest_due_date->lt(today());
    }

    public function totalOutstanding(): float
    {
        return (float) $this->outstanding_principal + (float) $this->outstanding_interest;
    }
}
