<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsappNotificationLog extends Model
{
    protected $fillable = [
        'loan_id',
        'due_date',
        'sent_at',
    ];

    protected $casts = [
        'due_date' => 'date',
        'sent_at' => 'datetime',
    ];

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }
}
