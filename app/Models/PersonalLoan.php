<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonalLoan extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'person_name',
        'amount',
        'loan_date',
        'notes',
        'is_returned',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'loan_date' => 'date',
        'is_returned' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
