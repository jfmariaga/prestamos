<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->string('borrower_name');
            $table->string('borrower_phone');
            $table->string('borrower_reference');
            $table->decimal('principal_amount', 12, 2);
            $table->decimal('outstanding_principal', 12, 2);
            $table->decimal('interest_rate', 5, 2);
            $table->decimal('outstanding_interest', 12, 2)->default(0);
            $table->date('loan_date');
            $table->date('next_interest_due_date');
            $table->enum('status', ['active', 'paid'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
