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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('tenant_id')->nullable();

            $table->string('type')->default('sms');
            $table->text('message');

            $table->string('status')->default('pending');

            $table->integer('attempts')->default(0);

            $table->timestamp('processed_at')->nullable();

            $table->timestamps();

            $table->index(['user_id']);
            $table->index(['status']);
            $table->index(['tenant_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
