<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('school_classes', function (Blueprint $table) {
            $table->decimal('monthly_tuition_fee', 10, 2)->default(0)->after('capacity');
        });

        if (Schema::hasTable('tuition_plans')) {
            DB::table('tuition_plans')->orderBy('id')->get()->each(function ($plan) {
                DB::table('school_classes')
                    ->where('academic_year_id', $plan->academic_year_id)
                    ->where('grade_id', $plan->grade_id)
                    ->where('branch_id', $plan->branch_id)
                    ->where('monthly_tuition_fee', 0)
                    ->update(['monthly_tuition_fee' => $plan->monthly_fee]);
            });
        }
    }

    public function down(): void
    {
        Schema::table('school_classes', function (Blueprint $table) {
            $table->dropColumn('monthly_tuition_fee');
        });
    }
};
