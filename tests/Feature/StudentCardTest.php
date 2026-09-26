<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Branch;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentCard;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentCardTest extends TestCase
{
    use RefreshDatabase;

    public function test_card_is_created_with_secure_token_and_public_verification_works(): void
    {
        $branch = Branch::create(['code' => 'CARD', 'name' => 'Card Branch', 'status' => 'active']);
        $user = User::factory()->create(['branch_id' => $branch->id, 'role' => 'super_admin']);
        $year = AcademicYear::create(['name' => '2026-2027', 'start_date' => '2026-09-01', 'end_date' => '2027-08-31', 'is_active' => true, 'branch_id' => $branch->id]);
        $grade = Grade::create(['name' => 'Grade 3', 'level' => 3, 'branch_id' => $branch->id]);
        $class = SchoolClass::create(['academic_year_id' => $year->id, 'grade_id' => $grade->id, 'name' => '3A', 'capacity' => 30, 'branch_id' => $branch->id]);
        $student = Student::create(['code' => 'CARD-001', 'name_kh' => 'សិស្សកាត', 'gender' => 'M', 'status' => 'active', 'branch_id' => $branch->id]);
        $enrollment = Enrollment::create(['student_id' => $student->id, 'branch_id' => $branch->id, 'academic_year_id' => $year->id, 'grade_id' => $grade->id, 'class_id' => $class->id, 'enrollment_date' => '2026-09-01', 'status' => 'active']);

        $response = $this->actingAs($user)->post(route('digital-cards.store'), ['student_id' => $student->id, 'enrollment_id' => $enrollment->id, 'academic_year_id' => $year->id, 'issue_date' => '2026-09-23', 'expiry_date' => '2027-09-22', 'card_type' => 'student']);
        $card = StudentCard::first();
        $response->assertRedirect(route('digital-cards.show', $card));
        $this->assertNotSame($student->id . '', $card->qr_token);
        $this->get(route('student-cards.verify', $card->qr_token))->assertOk()->assertSee('សិស្សកាត');
        $this->get(route('digital-cards.show', $card))->assertOk()->assertSee('<svg', false);
    }

    public function test_duplicate_active_card_for_year_is_rejected(): void
    {
        $branch = Branch::create(['code' => 'DUP', 'name' => 'Duplicate Branch', 'status' => 'active']);
        $user = User::factory()->create(['branch_id' => $branch->id, 'role' => 'super_admin']);
        $year = AcademicYear::create(['name' => '2026-2027', 'start_date' => '2026-09-01', 'end_date' => '2027-08-31', 'is_active' => true, 'branch_id' => $branch->id]);
        $grade = Grade::create(['name' => 'Grade 4', 'level' => 4, 'branch_id' => $branch->id]);
        $class = SchoolClass::create(['academic_year_id' => $year->id, 'grade_id' => $grade->id, 'name' => '4A', 'capacity' => 30, 'branch_id' => $branch->id]);
        $student = Student::create(['code' => 'DUP-001', 'name_kh' => 'សិស្សស្ទួន', 'gender' => 'F', 'status' => 'active', 'branch_id' => $branch->id]);
        $enrollment = Enrollment::create(['student_id' => $student->id, 'branch_id' => $branch->id, 'academic_year_id' => $year->id, 'grade_id' => $grade->id, 'class_id' => $class->id, 'enrollment_date' => '2026-09-01', 'status' => 'active']);
        $payload = ['student_id' => $student->id, 'enrollment_id' => $enrollment->id, 'academic_year_id' => $year->id, 'issue_date' => '2026-09-23', 'expiry_date' => '2027-09-22', 'card_type' => 'student'];
        $this->actingAs($user)->post(route('digital-cards.store'), $payload);
        $this->actingAs($user)->post(route('digital-cards.store'), $payload)->assertStatus(422);
        $this->assertDatabaseCount('student_cards', 1);
    }
}
