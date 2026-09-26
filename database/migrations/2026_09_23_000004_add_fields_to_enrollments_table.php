<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->foreignId('student_id')->after('id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->after('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->after('branch_id')->constrained()->restrictOnDelete();
            $table->foreignId('grade_id')->after('academic_year_id')->constrained()->restrictOnDelete();
            $table->foreignId('class_id')->after('grade_id')->constrained('school_classes')->restrictOnDelete();
            $table->date('enrollment_date')->after('class_id');
            $table->string('status')->default('active')->after('enrollment_date');
            $table->index(['student_id', 'academic_year_id']);
            $table->index(['branch_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropForeign(['student_id']);
            $table->dropForeign(['branch_id']);
            $table->dropForeign(['academic_year_id']);
            $table->dropForeign(['grade_id']);
            $table->dropForeign(['class_id']);
            $table->dropIndex(['student_id', 'academic_year_id']);
            $table->dropIndex(['branch_id', 'status']);
            $table->dropColumn(['student_id', 'branch_id', 'academic_year_id', 'grade_id', 'class_id', 'enrollment_date', 'status']);
        });
    }
};
