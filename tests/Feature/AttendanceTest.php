<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Branch;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Term;
use App\Models\User;
use App\Services\AttendanceCalculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    private function setupClass(): array
    {
        $branch = Branch::create(['code' => 'ATT', 'name' => 'Attendance Branch', 'status' => 'active']);
        $user = User::factory()->create(['branch_id' => $branch->id, 'role' => 'super_admin']);
        $year = AcademicYear::create(['name' => '2026-2027', 'start_date' => '2026-09-01', 'end_date' => '2027-08-31', 'is_active' => true, 'branch_id' => $branch->id]);
        $grade = Grade::create(['name' => 'Grade 2', 'level' => 2, 'branch_id' => $branch->id]);
        $class = SchoolClass::create(['academic_year_id' => $year->id, 'grade_id' => $grade->id, 'name' => '2A', 'capacity' => 30, 'branch_id' => $branch->id]);
        $term = Term::create(['name' => 'Term 1', 'academic_year_id' => $year->id, 'grade_id' => $grade->id, 'branch_id' => $branch->id, 'maximum_score' => 100, 'status' => 'active']);
        $student = Student::create(['code' => 'A-001', 'name_kh' => 'សិស្សវត្តមាន', 'gender' => 'F', 'status' => 'active', 'branch_id' => $branch->id]);
        Enrollment::create(['student_id' => $student->id, 'branch_id' => $branch->id, 'academic_year_id' => $year->id, 'grade_id' => $grade->id, 'class_id' => $class->id, 'enrollment_date' => '2026-09-01', 'status' => 'active']);
        return compact('branch', 'user', 'year', 'class', 'term', 'student');
    }

    public function test_bulk_attendance_is_saved_and_duplicate_date_is_updated(): void
    {
        $data = $this->setupClass();
        $payload = ['academic_year_id' => $data['year']->id, 'term_id' => $data['term']->id, 'class_id' => $data['class']->id, 'attendance_date' => '2026-09-23', 'records' => [['student_id' => $data['student']->id, 'status' => 'present', 'check_in_time' => '07:30']]];
        $this->actingAs($data['user'])->post(route('attendance.store'), $payload)->assertRedirect(route('attendance.index'));
        $payload['records'][0]['status'] = 'late';
        $this->actingAs($data['user'])->post(route('attendance.store'), $payload)->assertRedirect(route('attendance.index'));
        $this->assertDatabaseCount('attendances', 1);
        $this->assertDatabaseHas('attendances', ['student_id' => $data['student']->id, 'status' => 'late']);
    }

    public function test_excused_attendance_requires_reason_and_rate_counts_late_as_attendance(): void
    {
        $data = $this->setupClass();
        $payload = ['academic_year_id' => $data['year']->id, 'term_id' => $data['term']->id, 'class_id' => $data['class']->id, 'attendance_date' => '2026-09-23', 'records' => [['student_id' => $data['student']->id, 'status' => 'excused']]];
        $this->actingAs($data['user'])->post(route('attendance.store'), $payload)->assertSessionHasErrors('records.0.reason');
        $records = collect([new Attendance(['status' => 'present']), new Attendance(['status' => 'late']), new Attendance(['status' => 'absent']), new Attendance(['status' => 'excused'])]);
        $summary = AttendanceCalculationService::summary($records);
        $this->assertSame(50.0, $summary['rate']);
    }
}
