<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->renameColumn('account', 'project_value');
            $table->renameColumn('quotation_distribusi', 'internal_cost');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->renameColumn('project_value', 'account');
            $table->renameColumn('internal_cost', 'quotation_distribusi');
        });
    }
};
