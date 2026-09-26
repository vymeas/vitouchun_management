<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grades', function (Blueprint $table) {
            $table->decimal('monthly_tuition_fee', 10, 2)->default(0)->after('level');
        });

        DB::table('grades')->get()->each(function ($grade) {
            $fee = DB::table('school_classes')->where('grade_id', $grade->id)->where('monthly_tuition_fee', '>', 0)->orderByDesc('id')->value('monthly_tuition_fee');
            if ($fee !== null) DB::table('grades')->where('id', $grade->id)->update(['monthly_tuition_fee' => $fee]);
        });
    }

    public function down(): void
    {
        Schema::table('grades', function (Blueprint $table) {
            $table->dropColumn('monthly_tuition_fee');
        });
    }
};
