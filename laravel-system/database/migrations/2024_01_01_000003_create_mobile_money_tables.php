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
        // Users table
        Schema::create('mobile_users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->date('birth_date');
            $table->enum('gender', ['Male', 'Female', 'Other']);
            $table->boolean('is_active')->default(true);
            $table->string('phone')->unique();
            $table->timestamps();
        });

        // Groups table
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->string('group_name');
            $table->timestamps();
        });

        // Group members table
        Schema::create('group_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('groups')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('mobile_users')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['group_id', 'user_id']);
        });

        // Payments table
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('phone');
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('RWF');
            $table->string('reference')->unique();
            $table->enum('status', ['pending', 'completed', 'failed'])->default('pending');
            $table->string('transaction_id')->nullable();
            $table->timestamps();
        });

        // Transfers table
        Schema::create('transfers', function (Blueprint $table) {
            $table->id();
            $table->string('sender_phone');
            $table->string('receiver_phone');
            $table->decimal('amount', 10, 2);
            $table->text('message')->nullable();
            $table->enum('status', ['pending', 'completed', 'failed'])->default('pending');
            $table->string('transaction_id')->nullable();
            $table->timestamps();
        });

        // Admin users table
        Schema::create('admin_users', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('password_hash');
            $table->string('email')->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_users');
        Schema::dropIfExists('transfers');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('group_members');
        Schema::dropIfExists('groups');
        Schema::dropIfExists('mobile_users');
    }
};
