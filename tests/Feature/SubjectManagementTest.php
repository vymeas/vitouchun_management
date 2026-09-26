<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Grade;
use App\Models\Subject;
use App\Models\User;
use Database\Seeders\SubjectSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubjectManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_subject_code_is_generated_and_grade_assignment_is_saved(): void
    {
        $branch = Branch::create(['code' => 'SUB', 'name' => 'Subject Branch', 'status' => 'active']);
        $user = User::factory()->create(['branch_id' => $branch->id, 'role' => 'super_admin']);
        $grade = Grade::create(['name' => 'ថ្នាក់ទី ១', 'level' => 1, 'education_level' => 'primary', 'branch_id' => $branch->id]);

        $response = $this->actingAs($user)->post(route('subjects.store'), [
            'branch_id' => $branch->id,
            'name_kh' => 'គណិតវិទ្យា',
            'name_en' => 'Mathematics',
            'level' => 'primary',
            'credit' => 0,
            'type' => 'core',
            'subject_type' => 'គណិតវិទ្យា',
            'display_order' => 1,
            'status' => 'active',
            'grade_ids' => [$grade->id],
        ]);

        $subject = Subject::first();
        $response->assertRedirect(route('subjects.index'));
        $this->assertSame('SUB001', $subject->code);
        $this->assertTrue($subject->grades()->whereKey($grade->id)->exists());
        $this->assertDatabaseHas('grade_subjects', ['grade_id' => $grade->id, 'subject_id' => $subject->id]);
    }

    public function test_subject_cannot_assign_to_a_grade_from_another_education_level(): void
    {
        $branch = Branch::create(['code' => 'MIX', 'name' => 'Mixed Branch', 'status' => 'active']);
        $user = User::factory()->create(['branch_id' => $branch->id, 'role' => 'super_admin']);
        $grade = Grade::create(['name' => 'មត្តេយ្យ ៣', 'level' => 1, 'education_level' => 'preschool', 'branch_id' => $branch->id]);

        $response = $this->actingAs($user)->post(route('subjects.store'), [
            'branch_id' => $branch->id,
            'name_kh' => 'រៀនអាន',
            'level' => 'primary',
            'credit' => 0,
            'type' => 'core',
            'status' => 'active',
            'grade_ids' => [$grade->id],
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseCount('subjects', 0);
    }

    public function test_preschool_components_can_share_names_under_different_parents_but_not_the_same_parent(): void
    {
        $branch = Branch::create(['code' => 'CMP', 'name' => 'Component Branch', 'status' => 'active']);
        $user = User::factory()->create(['branch_id' => $branch->id, 'role' => 'super_admin']);
        $payload = ['branch_id' => $branch->id, 'level' => 'preschool', 'credit' => 0, 'type' => 'core', 'status' => 'active'];

        $this->actingAs($user)->post(route('subjects.store'), $payload + ['name_kh' => 'ភាសាខ្មែរ']);
        $mathResponse = $this->actingAs($user)->post(route('subjects.store'), $payload + ['name_kh' => 'គណិតវិទ្យា']);
        $khmer = Subject::where('name_kh', 'ភាសាខ្មែរ')->firstOrFail();
        $math = Subject::where('name_kh', 'គណិតវិទ្យា')->firstOrFail();

        $this->actingAs($user)->post(route('subjects.store'), $payload + ['name_kh' => 'រៀនអាន', 'parent_subject_id' => $khmer->id]);
        $mathResponse = $this->actingAs($user)->post(route('subjects.store'), $payload + ['name_kh' => 'រៀនអាន', 'parent_subject_id' => $math->id]);
        $duplicateResponse = $this->actingAs($user)->post(route('subjects.store'), $payload + ['name_kh' => 'រៀនអាន', 'parent_subject_id' => $khmer->id]);

        $mathResponse->assertRedirect(route('subjects.index'));
        $duplicateResponse->assertStatus(422);
        $this->assertSame(2, Subject::where('name_kh', 'រៀនអាន')->count());
        $this->assertSame(2, Subject::whereNotNull('parent_subject_id')->count());
    }

    public function test_subject_seeder_is_idempotent_and_assigns_only_existing_matching_grades(): void
    {
        $branch = Branch::create(['code' => 'SEED', 'name' => 'Seeder Branch', 'status' => 'active']);
        Grade::create(['name' => 'មត្តេយ្យ ៣', 'level' => 1, 'education_level' => 'preschool', 'branch_id' => $branch->id]);
        Grade::create(['name' => 'ថ្នាក់ទី ១', 'level' => 1, 'education_level' => 'primary', 'branch_id' => $branch->id]);

        $this->seed(SubjectSeeder::class);
        $firstCount = Subject::where('branch_id', $branch->id)->count();
        $this->seed(SubjectSeeder::class);
        $gradeIds = Grade::where('branch_id', $branch->id)->pluck('id');

        $this->assertSame(32, $firstCount);
        $this->assertSame($firstCount, Subject::where('branch_id', $branch->id)->count());
        $this->assertSame(32, \DB::table('grade_subjects')->whereIn('grade_id', $gradeIds)->count());
        $this->assertSame(2, Subject::where('branch_id', $branch->id)->where('name_kh', 'រៀនអាន')->whereNotNull('parent_subject_id')->count());
    }
}
