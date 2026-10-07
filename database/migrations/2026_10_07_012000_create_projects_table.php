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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();

            $table->foreignId('end_user_id')
                ->constrained('end_users')
                ->cascadeOnDelete();

            $table->foreignId('pic_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('project_name');

            $table->decimal('account', 15, 2)->nullable();

            $table->string('po_number')->nullable();

            $table->string('quotation_number')->nullable();

            $table->decimal('quotation_distribusi', 15, 2)->nullable();

            $table->decimal('margin', 15, 2)->nullable();

            $table->decimal('percentage', 8, 2)->nullable();

            $table->enum('status', ['planning', 'progress', 'done', 'cancel'])
                ->default('planning');

            $table->date('deadline_date')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
