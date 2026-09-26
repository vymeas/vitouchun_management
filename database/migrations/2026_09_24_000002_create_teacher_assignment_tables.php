<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('teacher_class_assignments')) Schema::create('teacher_class_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->restrictOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->string('role', 30)->default('class_teacher');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('status', 20)->default('active');
            $table->text('remark')->nullable();
            $table->timestamps();
            $table->index(['branch_id', 'status']);
        });
        if (Schema::hasTable('teacher_class_assignments')) {
            Schema::table('teacher_class_assignments', function (Blueprint $table) {
                $table->unique(['teacher_id', 'class_id', 'academic_year_id', 'role'], 'tca_teacher_class_year_role_uq');
            });
        }

        Schema::create('teacher_subject_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->restrictOnDelete();
            $table->foreignId('term_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('weekly_hours')->default(0);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamps();
            $table->unique(['teacher_id', 'class_id', 'subject_id', 'academic_year_id', 'term_id'], 'teacher_subject_unique');
            $table->index(['branch_id', 'status']);
        });

        Schema::create('teacher_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->restrictOnDelete();
            $table->foreignId('term_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->string('day_of_week', 15);
            $table->time('start_time');
            $table->time('end_time');
            $table->string('room')->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamps();
            $table->index(['branch_id', 'day_of_week', 'start_time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_schedules');
        Schema::dropIfExists('teacher_subject_assignments');
        Schema::dropIfExists('teacher_class_assignments');
    }
};
