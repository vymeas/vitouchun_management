<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_can_be_created_and_deactivated(): void
    {
        $branch = Branch::create(['code'=>'EMP','name'=>'Employee Branch','status'=>'active']);
        $user = User::factory()->create(['branch_id'=>$branch->id,'role'=>'super_admin']);
        $response = $this->actingAs($user)->post(route('employees.store'), ['name_kh'=>'បុគ្គលិកម្នាក់','name_en'=>'Employee One','gender'=>'M','phone'=>'012345678','branch_id'=>$branch->id,'joining_date'=>'2026-09-24','employment_type'=>'full_time','basic_salary'=>'350','status'=>'active']);
        $employee = Employee::first();
        $response->assertRedirect(route('employees.show',$employee));
        $this->assertSame('EMP-00001',$employee->employee_id);
        $this->actingAs($user)->delete(route('employees.destroy',$employee))->assertRedirect(route('employees.index'));
        $this->assertSoftDeleted($employee);
    }

    public function test_employee_branch_cannot_be_viewed_by_another_branch_user(): void
    {
        $branchA = Branch::create(['code'=>'EA','name'=>'A','status'=>'active']);
        $branchB = Branch::create(['code'=>'EB','name'=>'B','status'=>'active']);
        $employee = Employee::create(['employee_id'=>'EMP-00001','name_kh'=>'សិស្ស','gender'=>'F','branch_id'=>$branchB->id,'joining_date'=>'2026-09-24','employment_type'=>'full_time','basic_salary'=>0,'status'=>'active']);
        $user = User::factory()->create(['branch_id'=>$branchA->id,'role'=>'admin']);
        $this->actingAs($user)->get(route('employees.show',$employee))->assertForbidden();
    }
}
