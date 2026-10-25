<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanPayment extends Model
{
    protected $fillable = [
        'loan_id',
        'paid_at',
        'total_amount',
        'interest_amount',
        'principal_amount',
        'resulting_outstanding_principal',
        'resulting_outstanding_interest',
    ];

    protected $casts = [
        'paid_at' => 'date',
        'total_amount' => 'decimal:2',
        'interest_amount' => 'decimal:2',
        'principal_amount' => 'decimal:2',
        'resulting_outstanding_principal' => 'decimal:2',
        'resulting_outstanding_interest' => 'decimal:2',
    ];

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }
}
