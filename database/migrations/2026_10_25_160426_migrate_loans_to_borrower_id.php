<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->foreignId('borrower_id')->nullable()->after('user_id')->constrained()->restrictOnDelete();
        });

        $loans = DB::table('loans')
            ->orderBy('id')
            ->get(['id', 'borrower_name', 'borrower_phone', 'borrower_reference']);

        $borrowerIdsByPhone = [];

        foreach ($loans as $loan) {
            $phone = $loan->borrower_phone;

            if (! isset($borrowerIdsByPhone[$phone])) {
                $existing = DB::table('borrowers')->where('phone', $phone)->first();

                $borrowerIdsByPhone[$phone] = $existing
                    ? $existing->id
                    : DB::table('borrowers')->insertGetId([
                        'phone' => $phone,
                        'name' => $loan->borrower_name,
                        'reference' => $loan->borrower_reference,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
            } else {
                // Later loans reflect the most recent name/reference we have for this phone.
                DB::table('borrowers')->where('id', $borrowerIdsByPhone[$phone])->update([
                    'name' => $loan->borrower_name,
                    'reference' => $loan->borrower_reference,
                    'updated_at' => now(),
                ]);
            }

            DB::table('loans')->where('id', $loan->id)->update([
                'borrower_id' => $borrowerIdsByPhone[$phone],
            ]);
        }

        Schema::table('loans', function (Blueprint $table) {
            $table->dropColumn(['borrower_name', 'borrower_phone', 'borrower_reference']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->string('borrower_name')->nullable()->after('user_id');
            $table->string('borrower_phone')->nullable()->after('borrower_name');
            $table->string('borrower_reference')->nullable()->after('borrower_phone');
        });

        $loans = DB::table('loans')->whereNotNull('borrower_id')->get(['id', 'borrower_id']);

        foreach ($loans as $loan) {
            $borrower = DB::table('borrowers')->find($loan->borrower_id);

            if (! $borrower) {
                continue;
            }

            DB::table('loans')->where('id', $loan->id)->update([
                'borrower_name' => $borrower->name,
                'borrower_phone' => $borrower->phone,
                'borrower_reference' => $borrower->reference,
            ]);
        }

        Schema::table('loans', function (Blueprint $table) {
            $table->dropConstrainedForeignId('borrower_id');
        });
    }
};
