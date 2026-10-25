<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loan_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained()->cascadeOnDelete();
            $table->date('paid_at');
            $table->decimal('total_amount', 12, 2);
            $table->decimal('interest_amount', 12, 2);
            $table->decimal('principal_amount', 12, 2);
            $table->decimal('resulting_outstanding_principal', 12, 2);
            $table->decimal('resulting_outstanding_interest', 12, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_payments');
    }
};
