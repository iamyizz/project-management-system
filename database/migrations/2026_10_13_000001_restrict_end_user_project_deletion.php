<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table): void {
            $table->dropForeign(['end_user_id']);
            $table->foreign('end_user_id')
                ->references('id')
                ->on('end_users')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table): void {
            $table->dropForeign(['end_user_id']);
            $table->foreign('end_user_id')
                ->references('id')
                ->on('end_users')
                ->cascadeOnDelete();
        });
    }
};
