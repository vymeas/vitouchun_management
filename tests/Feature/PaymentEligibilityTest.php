<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Branch;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentEligibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_selection_excludes_students_without_active_enrollment(): void
    {
        $branch = Branch::create(['code' => 'ELG', 'name' => 'Eligibility Branch', 'status' => 'active']);
        $user = User::factory()->create(['branch_id' => $branch->id, 'role' => 'super_admin']);
        $student = Student::create(['code' => 'ELG-001', 'name_kh' => 'សិស្សមិនទាន់ចុះឈ្មោះ', 'gender' => 'M', 'status' => 'active', 'branch_id' => $branch->id]);

        $response = $this->actingAs($user)->get(route('payments.create'));

        $response->assertOk()->assertDontSee('សិស្សមិនទាន់ចុះឈ្មោះ');
    }

    public function test_payment_post_is_rejected_for_pending_enrollment(): void
    {
        $branch = Branch::create(['code' => 'PND', 'name' => 'Pending Branch', 'status' => 'active']);
        $user = User::factory()->create(['branch_id' => $branch->id, 'role' => 'super_admin']);
        $student = Student::create(['code' => 'PND-001', 'name_kh' => 'សិស្សរង់ចាំ', 'gender' => 'F', 'status' => 'active', 'branch_id' => $branch->id]);
        $year = AcademicYear::create(['name' => '2026-2027', 'start_date' => '2026-09-01', 'end_date' => '2027-08-31', 'is_active' => true, 'branch_id' => $branch->id]);
        $grade = Grade::create(['name' => 'Grade 1', 'level' => 1, 'branch_id' => $branch->id]);
        $class = SchoolClass::create(['name' => '1A', 'academic_year_id' => $year->id, 'grade_id' => $grade->id, 'capacity' => 30, 'branch_id' => $branch->id]);
        $enrollment = Enrollment::create(['student_id' => $student->id, 'branch_id' => $branch->id, 'academic_year_id' => $year->id, 'grade_id' => $grade->id, 'class_id' => $class->id, 'enrollment_date' => '2026-09-01', 'status' => 'pending']);

        $response = $this->actingAs($user)->post(route('payments.store'), [
            'student_id' => $student->id,
            'enrollment_id' => $enrollment->id,
            'academic_year_id' => $year->id,
            'payment_date' => '2026-09-24',
            'administrative_fee' => 0,
            'tuition_amount' => 30,
            'payment_method' => 'cash',
        ]);

        $response->assertSessionHasErrors('student_id');
        $this->assertDatabaseCount('payments', 0);
    }
}
