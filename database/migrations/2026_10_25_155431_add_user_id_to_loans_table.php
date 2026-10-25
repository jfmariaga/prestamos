<?php

use App\Models\Loan;
use App\Models\User;
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
            $table->foreignId('user_id')->nullable()->after('borrower_reference')->constrained()->nullOnDelete();
        });

        $firstUserId = User::query()->orderBy('id')->value('id');

        if ($firstUserId) {
            Loan::query()->whereNull('user_id')->update(['user_id' => $firstUserId]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });
    }
};
