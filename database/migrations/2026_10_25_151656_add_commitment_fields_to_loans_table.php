<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->date('commitment_date')->nullable()->after('loan_date');
            $table->decimal('agreed_interest_amount', 12, 2)->nullable()->after('commitment_date');
            $table->timestamp('commitment_broken_at')->nullable()->after('agreed_interest_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropColumn(['commitment_date', 'agreed_interest_amount', 'commitment_broken_at']);
        });
    }
};
