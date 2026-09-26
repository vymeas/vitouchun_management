<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportsTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_report_reads_database_and_exports_filtered_csv(): void
    {
        $branch = Branch::create(['code' => 'REP', 'name' => 'Report Branch', 'status' => 'active']);
        $user = User::factory()->create(['branch_id' => $branch->id, 'role' => 'super_admin']);
        Student::create(['code' => 'REP-001', 'name_kh' => 'សិស្សរបាយការណ៍', 'gender' => 'M', 'status' => 'active', 'branch_id' => $branch->id]);
        Student::create(['code' => 'REP-002', 'name_kh' => 'សិស្សផ្សេង', 'gender' => 'F', 'status' => 'inactive', 'branch_id' => $branch->id]);

        $this->actingAs($user)->get(route('reports.show', ['type' => 'students', 'search' => 'REP-001']))->assertOk()->assertSee('សិស្សរបាយការណ៍')->assertDontSee('សិស្សផ្សេង');
        $this->actingAs($user)->get(route('reports.export', ['type' => 'students', 'search' => 'REP-001']))->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_report_pdf_endpoint_is_real(): void
    {
        $branch = Branch::create(['code' => 'PDF', 'name' => 'PDF Branch', 'status' => 'active']);
        $user = User::factory()->create(['branch_id' => $branch->id, 'role' => 'super_admin']);
        $this->actingAs($user)->get(route('reports.pdf', 'students'))->assertOk()->assertHeader('content-type', 'application/pdf');
    }
}
