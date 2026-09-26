<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Student;
use App\Models\AcademicYear;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_branch_accountant_can_save_payment_for_own_branch(): void
    {
        $this->seed(PermissionSeeder::class);
        $branch = Branch::create(['code' => 'PAY', 'name' => 'Payment Branch', 'status' => 'active']);
        $accountant = User::factory()->create(['branch_id' => $branch->id, 'role' => 'accountant']);
        $student = Student::create(['code' => 'PAY-001', 'name_kh' => 'សិស្សបង់ប្រាក់', 'gender' => 'M', 'status' => 'active', 'branch_id' => $branch->id]);
        $year = AcademicYear::create(['name' => '2026-2027', 'start_date' => '2026-09-01', 'end_date' => '2027-08-31', 'is_active' => true, 'branch_id' => $branch->id]);
        $grade = Grade::create(['name' => 'Grade 1', 'level' => 1, 'branch_id' => $branch->id]);
        $class = SchoolClass::create(['name' => '1A', 'academic_year_id' => $year->id, 'grade_id' => $grade->id, 'capacity' => 30, 'branch_id' => $branch->id]);
        $enrollment = Enrollment::create(['student_id' => $student->id, 'branch_id' => $branch->id, 'academic_year_id' => $year->id, 'grade_id' => $grade->id, 'class_id' => $class->id, 'enrollment_date' => '2026-09-01', 'status' => 'active']);

        $response = $this->actingAs($accountant)->post(route('payments.store'), [
            'student_id' => $student->id,
            'enrollment_id' => $enrollment->id,
            'branch_id' => $branch->id,
            'payment_date' => '2026-09-24',
            'currency' => 'USD',
            'tuition_amount' => 25,
            'administrative_fee' => 0,
            'payment_method' => 'cash',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('payments', ['student_id' => $student->id, 'branch_id' => $branch->id, 'total_amount' => 25]);
    }

    public function test_branch_accountant_cannot_save_payment_for_another_branch(): void
    {
        $this->seed(PermissionSeeder::class);
        $ownBranch = Branch::create(['code' => 'OWN', 'name' => 'Own Branch', 'status' => 'active']);
        $otherBranch = Branch::create(['code' => 'OTH', 'name' => 'Other Branch', 'status' => 'active']);
        $accountant = User::factory()->create(['branch_id' => $ownBranch->id, 'role' => 'accountant']);
        $student = Student::create(['code' => 'OTH-001', 'name_kh' => 'សិស្សសាខាផ្សេង', 'gender' => 'F', 'status' => 'active', 'branch_id' => $otherBranch->id]);
        $year = AcademicYear::create(['name' => '2026-2027', 'start_date' => '2026-09-01', 'end_date' => '2027-08-31', 'is_active' => true, 'branch_id' => $otherBranch->id]);
        $grade = Grade::create(['name' => 'Grade 1', 'level' => 1, 'branch_id' => $otherBranch->id]);
        $class = SchoolClass::create(['name' => '1A', 'academic_year_id' => $year->id, 'grade_id' => $grade->id, 'capacity' => 30, 'branch_id' => $otherBranch->id]);
        $enrollment = Enrollment::create(['student_id' => $student->id, 'branch_id' => $otherBranch->id, 'academic_year_id' => $year->id, 'grade_id' => $grade->id, 'class_id' => $class->id, 'enrollment_date' => '2026-09-01', 'status' => 'active']);

        $this->actingAs($accountant)->post(route('payments.store'), [
            'student_id' => $student->id,
            'enrollment_id' => $enrollment->id,
            'branch_id' => $ownBranch->id,
            'payment_date' => '2026-09-24',
            'currency' => 'USD',
            'tuition_amount' => 25,
            'administrative_fee' => 0,
            'payment_method' => 'cash',
        ])->assertForbidden();

        $this->assertDatabaseCount('payments', 0);
    }
}
