<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('department_id')->constrained();
            $table->string('employee_id')->unique();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->date('hire_date');
            $table->string('position');
            $table->decimal('salary', 10, 2)->nullable();
            $table->string('profile_photo')->nullable();
            $table->boolean('face_registered')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};