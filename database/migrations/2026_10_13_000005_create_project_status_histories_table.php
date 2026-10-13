<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_status_histories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->string('project_name');
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('actor_name');
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->timestamps();

            $table->index(['project_id', 'created_at']);
        });

        DB::table('projects')->orderBy('id')->each(function (object $project): void {
            DB::table('project_status_histories')->insert([
                'project_id' => $project->id,
                'project_name' => $project->project_name,
                'actor_id' => null,
                'actor_name' => 'Sistem',
                'from_status' => null,
                'to_status' => $project->status,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_status_histories');
    }
};
