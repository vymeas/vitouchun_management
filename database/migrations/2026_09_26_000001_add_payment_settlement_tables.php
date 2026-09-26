<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->decimal('received_amount', 15, 2)->nullable()->after('total_amount');
            $table->index(['student_id', 'enrollment_id', 'status']);
        });

        Schema::create('payment_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_month_id')->nullable()->constrained('payment_months')->nullOnDelete();
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3)->default('USD');
            $table->timestamps();
            $table->index(['payment_id', 'payment_month_id']);
        });

        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained()->restrictOnDelete();
            $table->string('refund_no', 40)->unique();
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3)->default('USD');
            $table->string('reason')->nullable();
            $table->string('refund_method', 30)->default('cash');
            $table->date('refund_date');
            $table->string('status', 20)->default('completed');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('note')->nullable();
            $table->timestamps();
            $table->index(['payment_id', 'status']);
        });

        Schema::create('student_credits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->restrictOnDelete();
            $table->foreignId('enrollment_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount', 15, 2);
            $table->decimal('applied_amount', 15, 2)->default(0);
            $table->string('currency', 3)->default('USD');
            $table->string('status', 20)->default('available');
            $table->foreignId('source_payment_id')->nullable()->constrained('payments')->nullOnDelete();
            $table->text('note')->nullable();
            $table->timestamps();
            $table->index(['student_id', 'currency', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_credits');
        Schema::dropIfExists('refunds');
        Schema::dropIfExists('payment_allocations');
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['student_id', 'enrollment_id', 'status']);
            $table->dropColumn('received_amount');
        });
    }
};