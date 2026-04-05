<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * This migration handles existing installations that were created before B2C support.
 * Fresh installations already include these changes in create_maishapay_transactions_table.php.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Add motif column if it doesn't exist (fresh installs already have it)
        if (Schema::hasTable('maishapay_transactions') && ! Schema::hasColumn('maishapay_transactions', 'motif')) {
            Schema::table('maishapay_transactions', function (Blueprint $table) {
                $table->string('motif')->nullable()->after('wallet_id');
            });
        }

        // Extend the payment_type enum to include B2C (MySQL/MariaDB only — SQLite has no real enum)
        if (Schema::hasTable('maishapay_transactions') && DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE maishapay_transactions MODIFY COLUMN payment_type ENUM('MOBILEMONEY', 'CARD', 'B2C') NOT NULL");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE maishapay_transactions MODIFY COLUMN payment_type ENUM('MOBILEMONEY', 'CARD') NOT NULL");
        }

        if (Schema::hasColumn('maishapay_transactions', 'motif')) {
            Schema::table('maishapay_transactions', function (Blueprint $table) {
                $table->dropColumn('motif');
            });
        }
    }
};
