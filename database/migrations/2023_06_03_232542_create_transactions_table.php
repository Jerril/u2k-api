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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['transfer', 'withdrawal', 'deposit']);
            $table->json('sender');
            $table->json('receiver');
            $table->float('amount');
            $table->string('ref')->nullable();
            $table->string('paystack_ref')->nullable();
            $table->enum('state', ['initiated', 'failed', 'successful'])->default('initiated');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
