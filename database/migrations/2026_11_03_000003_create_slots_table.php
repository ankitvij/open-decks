<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('night_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('position');
            $table->dateTime('starts_at');
            // A slot holds at most one booking. The unique index plus the
            // conditional UPDATE in SlotClaimer make double-booking impossible,
            // even when two requests race for the same slot.
            $table->foreignId('booking_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->unique(['night_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('slots');
    }
};
