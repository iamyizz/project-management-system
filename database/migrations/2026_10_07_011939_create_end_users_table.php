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
        Schema::create('end_users', function (Blueprint $table) {
            $table->id();

            $table->string('nama');
            $table->string('industri')->nullable();
            $table->string('contact')->nullable();
            $table->string('telepon')->nullable();
            $table->string('email')->nullable();
            $table->string('kota')->nullable();
            $table->string('npwp')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('end_users');
    }
};
