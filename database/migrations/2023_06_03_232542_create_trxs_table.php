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
        Schema::create('trxs', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['transfer', 'withdrawal', 'deposit']);
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('receiver_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->float('amount');
            $table->string('ref')->nullable();
            $table->string('paystack_ref')->nullable();
            $table->json('details')->nullable();
            $table->enum('state', ['initiated', 'failed', 'successful'])->default('initiated');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trxs');
    }
};
