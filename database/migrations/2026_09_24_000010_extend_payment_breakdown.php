<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('payment_months', 'type')) {
            Schema::table('payment_months', function (Blueprint $table) {
                $table->string('type', 20)->default('tuition')->after('month_key');
            });
        }
        if (!Schema::hasColumn('payments', 'other_bank_name')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->string('other_bank_name')->nullable()->after('payment_method');
            });
        }
        if (!Schema::hasIndex('payment_months', 'payment_months_enrollment_year_month_type_unique')) {
            Schema::table('payment_months', function (Blueprint $table) {
                $table->unique(['enrollment_id', 'academic_year_id', 'month_key', 'type'], 'payment_months_enrollment_year_month_type_unique');
            });
        }
        if (Schema::hasIndex('payment_months', 'payment_months_enrollment_id_academic_year_id_month_key_unique')) {
            Schema::table('payment_months', function (Blueprint $table) {
                $table->dropUnique('payment_months_enrollment_id_academic_year_id_month_key_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::table('payment_months', function (Blueprint $table) {
            if (Schema::hasIndex('payment_months', 'payment_months_enrollment_year_month_type_unique')) $table->dropUnique('payment_months_enrollment_year_month_type_unique');
            $table->unique(['enrollment_id', 'academic_year_id', 'month_key']);
            $table->dropColumn('type');
        });
        if (Schema::hasColumn('payments', 'other_bank_name')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->dropColumn('other_bank_name');
            });
        }
    }
};
