<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Borrower extends Model
{
    /** @use HasFactory<\Database\Factories\BorrowerFactory> */
    use HasFactory;

    protected $fillable = [
        'phone',
        'name',
        'reference',
    ];

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }
}
