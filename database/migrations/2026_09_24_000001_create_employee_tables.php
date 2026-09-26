<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30);
            $table->string('name_kh');
            $table->string('name_en')->nullable();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('manager_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
            $table->unique(['code', 'branch_id']);
        });

        Schema::create('positions', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30);
            $table->string('name_kh');
            $table->string('name_en')->nullable();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
            $table->unique(['code', 'branch_id']);
        });

        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id', 40)->unique();
            $table->string('name_kh');
            $table->string('name_en')->nullable();
            $table->enum('gender', ['M', 'F', 'O']);
            $table->date('dob')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('emergency_contact')->nullable();
            $table->string('emergency_phone', 30)->nullable();
            $table->string('photo')->nullable();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('position_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->date('joining_date');
            $table->string('employment_type', 30)->default('full_time');
            $table->decimal('basic_salary', 15, 2)->default(0);
            $table->string('status', 30)->default('active');
            $table->text('remark')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['branch_id', 'status']);
            $table->index(['department_id', 'position_id']);
        });

        Schema::table('departments', function (Blueprint $table) {
            $table->foreign('manager_id')->references('id')->on('employees')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
        Schema::dropIfExists('positions');
        Schema::dropIfExists('departments');
    }
};
