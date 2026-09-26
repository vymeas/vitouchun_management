<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_registration_creates_user_employee_and_profile(): void
    {
        $branch=Branch::create(['code'=>'REG','name'=>'Registration Branch','status'=>'active']);
        $admin=User::factory()->create(['branch_id'=>$branch->id,'role'=>'super_admin']);
        $response=$this->actingAs($admin)->post(route('teachers.registration.store'),['full_name_kh'=>'គ្រូថ្មី','full_name'=>'New Teacher','username'=>'newteacher','email'=>'teacher@example.com','password'=>'password123','password_confirmation'=>'password123','gender'=>'F','branch_id'=>$branch->id,'joining_date'=>'2026-09-24','basic_salary'=>400]);
        $user=User::where('username','newteacher')->first();
        $response->assertRedirect(route('teachers.show',$user));
        $this->assertSame('teacher',$user->role);
        $this->assertNotNull($user->teacherProfile);
        $this->assertDatabaseHas('employees',['user_id'=>$user->id,'name_kh'=>'គ្រូថ្មី']);
        $this->assertStringStartsWith('TCH-',$user->teacherProfile->teacher_code);
    }
}
