<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('student_code')->nullable()->unique()->after('id');
            $table->string('khmer_name')->nullable()->after('name_kh');
            $table->string('english_name')->nullable()->after('name_en');
            $table->date('date_of_birth')->nullable()->after('gender');
            $table->text('place_of_birth')->nullable()->after('date_of_birth');
            $table->text('current_address')->nullable()->after('place_of_birth');
            $table->string('father_name')->nullable()->after('current_address');
            $table->string('father_occupation')->nullable()->after('father_name');
            $table->string('father_phone')->nullable()->after('father_occupation');
            $table->string('mother_name')->nullable()->after('father_phone');
            $table->string('mother_occupation')->nullable()->after('mother_name');
            $table->string('mother_phone')->nullable()->after('mother_occupation');
            $table->string('emergency_contact_name')->nullable()->after('mother_phone');
            $table->string('emergency_contact_phone')->nullable()->after('emergency_contact_name');
            $table->string('student_type')->nullable()->after('emergency_contact_phone');
            $table->text('health_condition')->nullable()->after('student_type');
            $table->text('characteristics')->nullable()->after('health_condition');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn([
                'student_code',
                'khmer_name',
                'english_name',
                'date_of_birth',
                'place_of_birth',
                'current_address',
                'father_name',
                'father_occupation',
                'father_phone',
                'mother_name',
                'mother_occupation',
                'mother_phone',
                'emergency_contact_name',
                'emergency_contact_phone',
                'student_type',
                'health_condition',
                'characteristics',
            ]);
        });
    }
};
