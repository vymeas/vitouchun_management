<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('payments', 'service_id')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->foreignId('service_id')->nullable()->after('enrollment_id')->constrained('services')->nullOnDelete();
            });
        }
        if (!Schema::hasIndex('payments', 'payments_student_enrollment_year_branch_idx')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->index(['student_id', 'enrollment_id', 'academic_year_id', 'branch_id'], 'payments_student_enrollment_year_branch_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasIndex('payments', 'payments_student_enrollment_year_branch_idx')) $table->dropIndex('payments_student_enrollment_year_branch_idx');
            if (Schema::hasColumn('payments', 'service_id')) {
                $table->dropForeign(['service_id']);
                $table->dropColumn('service_id');
            }
        });
    }
};
