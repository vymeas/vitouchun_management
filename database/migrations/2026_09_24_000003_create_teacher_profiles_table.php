<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->string('teacher_code', 40)->unique();
            $table->string('national_id')->nullable();
            $table->string('specialization')->nullable();
            $table->string('education_level')->nullable();
            $table->string('major')->nullable();
            $table->string('institution')->nullable();
            $table->unsignedSmallInteger('graduation_year')->nullable();
            $table->text('remark')->nullable();
            $table->timestamps();
            $table->index('branch_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_profiles');
    }
};
