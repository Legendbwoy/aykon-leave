<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->dateTime('check_in')->nullable(); // Make nullable for absent records
            $table->dateTime('check_out')->nullable();
            $table->string('check_in_method')->nullable();
            $table->string('check_out_method')->nullable();
            $table->string('check_in_photo')->nullable();
            $table->string('check_out_photo')->nullable();
            $table->decimal('check_in_confidence', 5, 2)->nullable();
            $table->decimal('check_out_confidence', 5, 2)->nullable();
            $table->decimal('work_hours', 5, 2)->nullable();
            $table->enum('status', ['present', 'absent', 'late', 'half-day', 'overtime'])->default('present');
            $table->text('notes')->nullable();
            $table->date('date')->nullable(); // Add date field for absent records
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};