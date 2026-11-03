<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('night_id')->constrained()->cascadeOnDelete();
            $table->string('dj_name');
            $table->string('email');
            $table->string('genre')->nullable();
            $table->string('mix_url')->nullable();
            $table->string('status', 20);
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index(['night_id', 'status', 'created_at']);
            $table->index(['night_id', 'email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
