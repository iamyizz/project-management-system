<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_activities', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->string('project_name');
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('actor_name');
            $table->string('field');
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->timestamps();

            $table->index(['project_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_activities');
    }
};
