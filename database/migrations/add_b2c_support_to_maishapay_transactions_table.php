<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('maishapay_transactions', function (Blueprint $table) {
            $table->string('motif')->nullable()->after('wallet_id');
        });

        // Alter the payment_type enum to include B2C
        DB::statement("ALTER TABLE maishapay_transactions MODIFY COLUMN payment_type ENUM('MOBILEMONEY', 'CARD', 'B2C') NOT NULL");
    }

    public function down(): void
    {
        // Revert payment_type enum (remove B2C)
        DB::statement("ALTER TABLE maishapay_transactions MODIFY COLUMN payment_type ENUM('MOBILEMONEY', 'CARD') NOT NULL");

        Schema::table('maishapay_transactions', function (Blueprint $table) {
            $table->dropColumn('motif');
        });
    }
};
