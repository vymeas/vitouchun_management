<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->string('level', 20)->nullable()->after('name_en');
            $table->string('subject_type', 50)->nullable()->after('type');
            $table->unsignedInteger('display_order')->default(0)->after('subject_type');
            $table->string('status', 20)->default('active')->after('display_order');
            $table->text('description')->nullable()->after('status');
            $table->index(['branch_id', 'level', 'status']);
            $table->unique(['branch_id', 'code']);
        });

        Schema::table('grades', function (Blueprint $table) {
            $table->string('education_level', 20)->nullable()->after('name');
            $table->index(['branch_id', 'education_level']);
        });

        Schema::create('grade_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grade_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('display_order')->default(0);
            $table->string('status', 20)->default('active');
            $table->timestamps();
            $table->unique(['grade_id', 'subject_id']);
            $table->index(['subject_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grade_subjects');
        Schema::table('grades', function (Blueprint $table) {
            $table->dropIndex(['branch_id', 'education_level']);
            $table->dropColumn('education_level');
        });
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropUnique(['branch_id', 'code']);
            $table->dropIndex(['branch_id', 'level', 'status']);
            $table->dropColumn(['level', 'subject_type', 'display_order', 'status', 'description']);
        });
    }
};
