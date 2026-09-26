<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Branch;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Term;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScoreTest extends TestCase
{
    use RefreshDatabase;

    private function setupClass(): array
    {
        $branch = Branch::create(['code' => 'SCO', 'name' => 'Score Branch', 'status' => 'active']);
        $user = User::factory()->create(['branch_id' => $branch->id, 'role' => 'super_admin']);
        $year = AcademicYear::create(['name' => '2026-2027', 'start_date' => '2026-09-01', 'end_date' => '2027-08-31', 'is_active' => true, 'branch_id' => $branch->id]);
        $grade = Grade::create(['name' => 'Grade 1', 'level' => 1, 'branch_id' => $branch->id]);
        $class = SchoolClass::create(['academic_year_id' => $year->id, 'grade_id' => $grade->id, 'name' => '1A', 'capacity' => 30, 'branch_id' => $branch->id]);
        $student = Student::create(['code' => 'S-001', 'name_kh' => 'សិស្សមួយ', 'gender' => 'M', 'status' => 'active', 'branch_id' => $branch->id]);
        Enrollment::create(['student_id' => $student->id, 'branch_id' => $branch->id, 'academic_year_id' => $year->id, 'grade_id' => $grade->id, 'class_id' => $class->id, 'enrollment_date' => '2026-09-01', 'status' => 'active']);
        $subject = Subject::create(['code' => 'KH', 'name_kh' => 'ភាសាខ្មែរ', 'credit' => 1, 'type' => 'core', 'branch_id' => $branch->id]);
        $term = Term::create(['name' => 'Term 1', 'academic_year_id' => $year->id, 'grade_id' => $grade->id, 'branch_id' => $branch->id, 'maximum_score' => 100, 'status' => 'active']);
        return compact('branch', 'user', 'year', 'class', 'student', 'subject', 'term');
    }

    public function test_bulk_scores_are_saved_with_calculated_result(): void
    {
        $data = $this->setupClass();
        $response = $this->actingAs($data['user'])->post(route('scores.store'), ['academic_year_id' => $data['year']->id, 'class_id' => $data['class']->id, 'subject_id' => $data['subject']->id, 'term_id' => $data['term']->id, 'scores' => [['student_id' => $data['student']->id, 'score' => 85]]]);
        $response->assertRedirect(route('scores.index'));
        $this->assertDatabaseHas('scores', ['student_id' => $data['student']->id, 'score' => 85, 'grade' => 'B', 'result' => 'passed']);
    }

    public function test_score_above_maximum_is_rejected(): void
    {
        $data = $this->setupClass();
        $response = $this->actingAs($data['user'])->post(route('scores.store'), ['academic_year_id' => $data['year']->id, 'class_id' => $data['class']->id, 'subject_id' => $data['subject']->id, 'term_id' => $data['term']->id, 'scores' => [['student_id' => $data['student']->id, 'score' => 101]]]);
        $response->assertStatus(422);
        $this->assertDatabaseCount('scores', 0);
    }
}
