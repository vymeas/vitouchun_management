<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->foreignId('parent_subject_id')->nullable()->after('branch_id')->constrained('subjects')->nullOnDelete();
            $table->index(['branch_id', 'parent_subject_id', 'level']);
            $table->unique(['branch_id', 'parent_subject_id', 'name_kh']);
        });
    }

    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropUnique(['branch_id', 'parent_subject_id', 'name_kh']);
            $table->dropIndex(['branch_id', 'parent_subject_id', 'level']);
            $table->dropForeign(['parent_subject_id']);
            $table->dropColumn('parent_subject_id');
        });
    }
};
