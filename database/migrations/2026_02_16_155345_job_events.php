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
        Schema::create('job_events', function (Blueprint $table) {
            $table->id();
            $table->uuid('job_uuid');
            $table->string('job_type');
            $table->string('queue');
            $table->string('state');
            $table->integer('attempt')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();

            $table->index(['job_uuid', 'state']);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
