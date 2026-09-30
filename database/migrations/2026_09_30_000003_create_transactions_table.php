<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_number')->nullable()->unique();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('subtotal')->default(0);
            $table->integer('discount')->default(0);
            $table->integer('tax')->default(0);
            $table->integer('other_fee')->default(0);
            $table->integer('grand_total')->default(0);
            $table->integer('paid_amount')->default(0);
            $table->integer('change_amount')->default(0);
            $table->string('payment_method')->default('Tunai');
            $table->string('status')->default('paid');
            $table->timestamps();

            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
