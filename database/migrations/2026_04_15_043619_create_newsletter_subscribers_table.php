<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Newsletter subscribers with double-opt-in confirmation.
     *
     * Email is encrypted at rest via CipherSweet (text column) and has a
     * blind index for duplicate-check lookups. Confirmation is required
     * before the subscriber is included in any outbound digest.
     */
    public function up(): void
    {
        Schema::create('newsletter_subscribers', function (Blueprint $table) {
            $table->id();
            $table->text('email'); // Encrypted via CipherSweet (blind index stored in shared `blind_indexes` table)
            $table->string('confirmation_token', 64)->unique();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('unsubscribed_at')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('newsletter_subscribers');
    }
};
