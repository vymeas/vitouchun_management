<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Remove default 'name' column and replace with our fields
            $table->dropColumn('name');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 50)->unique()->after('id');
            $table->string('full_name')->after('username');
            $table->string('full_name_kh')->nullable()->after('full_name');
            $table->string('phone', 20)->nullable()->after('email');
            $table->foreignId('branch_id')->nullable()->after('phone')->constrained('branches')->nullOnDelete();
            $table->enum('role', ['super_admin', 'admin', 'accountant', 'registrar', 'teacher', 'staff'])->default('staff')->after('branch_id');
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active')->after('role');
            $table->boolean('is_deleted')->default(false)->after('status');
            $table->timestamp('last_login_at')->nullable()->after('is_deleted');

            $table->index('branch_id');
            $table->index('role');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropIndex(['branch_id']);
            $table->dropIndex(['role']);
            $table->dropIndex(['status']);
            $table->dropColumn([
                'username', 'full_name', 'full_name_kh', 'phone',
                'branch_id', 'role', 'status', 'is_deleted', 'last_login_at'
            ]);
            $table->string('name')->after('id');
        });
    }
};
