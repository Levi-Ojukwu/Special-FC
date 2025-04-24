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
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name')->after('id');
            $table->string('last_name')->after('first_name');
            $table->string('username')->unique()->after('last_name');
            $table->foreignId('team_id')->nullable()->after('email')->constrained('teams')->onDelete('set null');
            $table->boolean('is_admin')->default(false);
            $table->boolean('is_verified')->default(false);
            $table->dropColumn('name'); // Remove default name column
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('name');
            $table->dropColumn(['first_name', 'last_name', 'username', 'team_id', 'is_admin', 'is_verified']);
        });
    }
};
