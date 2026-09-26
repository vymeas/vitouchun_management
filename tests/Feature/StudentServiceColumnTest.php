<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Branch;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\Payment;
use App\Models\PaymentItem;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentServiceColumnTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_list_shows_services_from_current_enrollment_payment_only(): void
    {
        $branch = Branch::create(['code' => 'SVC', 'name' => 'Service Branch', 'status' => 'active']);
        $user = User::factory()->create(['branch_id' => $branch->id, 'role' => 'super_admin']);
        $grade = Grade::create(['name' => 'ថ្នាក់ទី ៣', 'level' => 3, 'education_level' => 'primary', 'branch_id' => $branch->id]);
        $oldYear = AcademicYear::create(['name' => '2025-2026', 'start_date' => '2025-09-01', 'end_date' => '2026-08-31', 'is_active' => false, 'branch_id' => $branch->id]);
        $currentYear = AcademicYear::create(['name' => '2026-2027', 'start_date' => '2026-09-01', 'end_date' => '2027-08-31', 'is_active' => true, 'branch_id' => $branch->id]);
        $oldClass = SchoolClass::create(['name' => '2A', 'academic_year_id' => $oldYear->id, 'grade_id' => $grade->id, 'capacity' => 30, 'branch_id' => $branch->id]);
        $currentClass = SchoolClass::create(['name' => '3A', 'academic_year_id' => $currentYear->id, 'grade_id' => $grade->id, 'capacity' => 30, 'branch_id' => $branch->id]);
        $student = Student::create(['code' => 'SVC-001', 'name_kh' => 'សិស្សសេវា', 'gender' => 'M', 'status' => 'active', 'branch_id' => $branch->id]);
        $oldEnrollment = Enrollment::create(['student_id' => $student->id, 'branch_id' => $branch->id, 'academic_year_id' => $oldYear->id, 'grade_id' => $grade->id, 'class_id' => $oldClass->id, 'enrollment_date' => '2025-09-01', 'status' => 'completed']);
        $currentEnrollment = Enrollment::create(['student_id' => $student->id, 'branch_id' => $branch->id, 'academic_year_id' => $currentYear->id, 'grade_id' => $grade->id, 'class_id' => $currentClass->id, 'enrollment_date' => '2026-09-01', 'status' => 'active']);
        $oldPayment = Payment::create(['reference' => 'PAY-OLD', 'student_id' => $student->id, 'enrollment_id' => $oldEnrollment->id, 'academic_year_id' => $oldYear->id, 'branch_id' => $branch->id, 'payment_date' => '2025-09-02', 'currency' => 'USD', 'tuition_amount' => 10, 'administrative_fee' => 0, 'total_amount' => 10, 'payment_method' => 'cash', 'status' => 'posted']);
        PaymentItem::create(['payment_id' => $oldPayment->id, 'description' => 'Old Service', 'amount' => 10]);
        $currentPayment = Payment::create(['reference' => 'PAY-CURRENT', 'student_id' => $student->id, 'enrollment_id' => $currentEnrollment->id, 'academic_year_id' => $currentYear->id, 'branch_id' => $branch->id, 'payment_date' => '2026-09-02', 'currency' => 'USD', 'tuition_amount' => 20, 'administrative_fee' => 0, 'total_amount' => 20, 'payment_method' => 'cash', 'status' => 'posted']);
        PaymentItem::create(['payment_id' => $currentPayment->id, 'description' => 'Current Service A', 'amount' => 10]);
        PaymentItem::create(['payment_id' => $currentPayment->id, 'description' => 'Current Service B', 'amount' => 10]);

        $response = $this->actingAs($user)->get(route('students.index'));

        $response->assertOk()->assertSee('Current Service A')->assertSee('Current Service B')->assertDontSee('Old Service');
    }

    public function test_student_without_current_payment_shows_empty_service_fallback(): void
    {
        $branch = Branch::create(['code' => 'NOP', 'name' => 'No Payment Branch', 'status' => 'active']);
        $user = User::factory()->create(['branch_id' => $branch->id, 'role' => 'super_admin']);
        Student::create(['code' => 'NOP-001', 'name_kh' => 'សិស្សមិនទាន់បង់', 'gender' => 'F', 'status' => 'active', 'branch_id' => $branch->id]);

        $response = $this->actingAs($user)->get(route('students.index'));

        $response->assertOk()->assertSee('សិស្សមិនទាន់បង់')->assertSee('—');
    }
}
