<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transfers', function (Blueprint $table): void {
            $table->id();
            $table->string('public_token', 80)->unique();
            $table->string('sender_email')->nullable();
            $table->string('recipient_email');
            $table->text('message')->nullable();
            $table->string('password_hash')->nullable();
            $table->timestamp('expires_at')->index();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('notification_sent_at')->nullable();
            $table->unsignedInteger('max_downloads')->nullable();
            $table->unsignedInteger('download_count')->default(0);
            $table->string('status', 24)->default('pending')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transfers');
    }
};
