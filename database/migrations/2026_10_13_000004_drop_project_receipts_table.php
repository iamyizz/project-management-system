<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('project_receipts');
    }

    public function down(): void
    {
        Schema::create('project_receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('receipt_number')->nullable()->unique();
            $table->string('project_name');
            $table->string('received_from');
            $table->decimal('amount', 15, 2);
            $table->text('description');
            $table->date('issued_at');
            $table->timestamps();
        });
    }
};
