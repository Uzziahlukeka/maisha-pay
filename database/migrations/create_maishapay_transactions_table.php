<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maishapay_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_reference')->unique()->nullable();
            $table->enum('payment_type', ['MOBILEMONEY', 'CARD']);
            $table->string('provider');
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3);
            $table->string('customer_full_name')->nullable();
            $table->string('customer_firstname')->nullable();
            $table->string('customer_lastname')->nullable();
            $table->string('customer_email');
            $table->string('customer_phone')->nullable();
            $table->string('customer_address')->nullable();
            $table->string('customer_city')->nullable();
            $table->string('wallet_id')->nullable();
            $table->string('callback_url')->nullable();
            $table->enum('status', ['PENDING', 'SUCCESS', 'FAILED', 'CANCELLED'])->default('PENDING');
            $table->json('api_response')->nullable();
            $table->json('callback_data')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['provider', 'payment_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maishapay_transactions');
    }
};
