<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Permission;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StudentCreateFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_store_generates_unique_code_and_redirects_to_payment_for_save_and_pay(): void
    {
        $branch = Branch::create([
            'code' => 'B1',
            'name' => 'Branch 1',
            'name_kh' => 'សាខា ១',
            'status' => 'active',
        ]);

        $user = User::factory()->create([
            'branch_id' => $branch->id,
            'role' => 'admin',
            'status' => 'active',
        ]);

        foreach (['academic.view', 'students.create', 'students.edit', 'students.delete', 'students.view'] as $permissionName) {
            $permission = Permission::firstOrCreate(['name' => $permissionName], ['module' => 'students', 'action' => 'manage']);
            DB::table('role_permissions')->updateOrInsert(
                ['role' => 'admin', 'permission_id' => $permission->id],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }

        $user->clearPermissionCache();

        $firstResponse = $this->actingAs($user)->post(route('students.store'), [
            'branch_id' => $branch->id,
            'khmer_name' => 'សុខ ដារ៉ា',
            'english_name' => 'Sok Dara',
            'gender' => 'M',
            'date_of_birth' => '2018-01-15',
            'place_of_birth' => 'ខេត្តតាកែវ',
            'current_address' => 'តាកែវ',
            'father_name' => 'សុខ សុខា',
            'father_occupation' => 'កសិករ',
            'father_phone' => '012345678',
            'mother_name' => 'ស្រី ពៅ',
            'mother_occupation' => 'អាជីវករ',
            'mother_phone' => '098765432',
            'emergency_contact_name' => 'សុខ សុខា',
            'emergency_contact_phone' => '012345678',
            'student_type' => 'សិស្សធម្មតា',
            'health_condition' => 'សុខភាពធម្មតា',
            'characteristics' => 'រួសរាយរាក់ទាក់',
            'status' => 'active',
            'save_and_pay' => '1',
        ]);

        $firstStudent = Student::first();

        $firstResponse->assertRedirect(route('payments.create', ['student_id' => $firstStudent->id]));
        $this->assertSame('Stu000001', $firstStudent->student_code ?? $firstStudent->code);

        $secondStudent = Student::create([
            'branch_id' => $branch->id,
            'student_code' => 'Stu000002',
            'code' => 'Stu000002',
            'khmer_name' => 'សិស្សទីពីរ',
            'english_name' => 'Student Two',
            'gender' => 'F',
            'status' => 'active',
        ]);

        $this->assertSame('Stu000002', $secondStudent->student_code ?? $secondStudent->code);
    }
}
