<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Permission;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StudentBranchFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_filter_students_by_branch_or_view_all(): void
    {
        $branchOne = Branch::create(['code' => 'B1', 'name' => 'Branch One', 'status' => 'active']);
        $branchTwo = Branch::create(['code' => 'B2', 'name' => 'Branch Two', 'status' => 'active']);
        $user = User::factory()->create(['role' => 'super_admin', 'branch_id' => null]);
        Student::create(['code' => 'B1-001', 'name_kh' => 'សិស្សសាខាមួយ', 'gender' => 'M', 'status' => 'active', 'branch_id' => $branchOne->id]);
        Student::create(['code' => 'B2-001', 'name_kh' => 'សិស្សសាខាពីរ', 'gender' => 'F', 'status' => 'active', 'branch_id' => $branchTwo->id]);

        $filtered = $this->actingAs($user)->get(route('students.index', ['branch_id' => $branchOne->id]));
        $filtered->assertSee('សិស្សសាខាមួយ')->assertDontSee('សិស្សសាខាពីរ');

        $all = $this->actingAs($user)->get(route('students.index', ['branch_id' => 'all']));
        $all->assertSee('សិស្សសាខាមួយ')->assertSee('សិស្សសាខាពីរ');
    }

    public function test_non_super_admin_cannot_escape_assigned_branch_with_filter(): void
    {
        $ownBranch = Branch::create(['code' => 'OWN', 'name' => 'Own Branch', 'status' => 'active']);
        $otherBranch = Branch::create(['code' => 'OTH', 'name' => 'Other Branch', 'status' => 'active']);
        $user = User::factory()->create(['role' => 'registrar', 'branch_id' => $ownBranch->id]);
        $permission = Permission::firstOrCreate(['name' => 'academic.view'], ['module' => 'academic', 'action' => 'view']);
        DB::table('role_permissions')->insert(['role' => 'registrar', 'permission_id' => $permission->id, 'created_at' => now(), 'updated_at' => now()]);
        $user->clearPermissionCache();
        Student::create(['code' => 'OWN-001', 'name_kh' => 'សិស្សសាខាខ្លួន', 'gender' => 'M', 'status' => 'active', 'branch_id' => $ownBranch->id]);
        Student::create(['code' => 'OTH-001', 'name_kh' => 'សិស្សសាខាផ្សេង', 'gender' => 'F', 'status' => 'active', 'branch_id' => $otherBranch->id]);

        $response = $this->actingAs($user)->get(route('students.index', ['branch_id' => $otherBranch->id]));

        $response->assertOk()->assertSee('សិស្សសាខាខ្លួន')->assertDontSee('សិស្សសាខាផ្សេង');
    }
}
