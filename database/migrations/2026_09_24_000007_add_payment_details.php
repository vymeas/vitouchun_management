<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('discount_type', 10)->nullable()->after('administrative_fee');
            $table->decimal('discount_amount', 15, 2)->default(0)->after('discount_type');
            $table->index(['enrollment_id', 'academic_year_id', 'status']);
        });

        Schema::create('payment_months', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('enrollment_id')->constrained()->restrictOnDelete();
            $table->foreignId('academic_year_id')->constrained()->restrictOnDelete();
            $table->string('month_key', 7);
            $table->date('month_start');
            $table->timestamps();
            $table->unique(['enrollment_id', 'academic_year_id', 'month_key']);
            $table->index(['payment_id', 'month_start']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_months');
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['enrollment_id', 'academic_year_id', 'status']);
            $table->dropColumn(['discount_type', 'discount_amount']);
        });
    }
};
