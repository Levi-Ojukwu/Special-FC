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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->enum('type', ['registration', 'monthly_dues', 'other'])->default('monthly_dues');
            $table->decimal('amount', 10, 2);
            $table->string('receipt_image')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->dateTime('payment_date');
            $table->dateTime('expiry_date')->nullable();
            $table->integer('progress_percentage')->default(0);
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
