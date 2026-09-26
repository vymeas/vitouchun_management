<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Branch;
use App\Models\Expense;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\Payment;
use App\Models\Service;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use App\Services\KhmerAmountService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_creates_invoice_and_balanced_journal_entry(): void
    {
        $branch = Branch::create(['code' => 'FIN', 'name' => 'Finance Branch', 'status' => 'active']);
        $user = User::factory()->create(['branch_id' => $branch->id, 'role' => 'super_admin']);
        $student = Student::create(['code' => 'STU-001', 'name_kh' => 'សិស្ស', 'gender' => 'M', 'status' => 'active', 'branch_id' => $branch->id]);
        $year = AcademicYear::create(['name' => '2026-2027', 'start_date' => '2026-09-01', 'end_date' => '2027-08-31', 'is_active' => true, 'branch_id' => $branch->id]);
        $grade = Grade::create(['name' => 'Grade 1', 'level' => 1, 'branch_id' => $branch->id]);
        $class = SchoolClass::create(['name' => '1A', 'academic_year_id' => $year->id, 'grade_id' => $grade->id, 'capacity' => 30, 'branch_id' => $branch->id]);
        $enrollment = Enrollment::create(['student_id' => $student->id, 'branch_id' => $branch->id, 'academic_year_id' => $year->id, 'grade_id' => $grade->id, 'class_id' => $class->id, 'enrollment_date' => '2026-09-01', 'status' => 'active']);

        $response = $this->actingAs($user)->post(route('payments.store'), [
            'student_id' => $student->id,
            'enrollment_id' => $enrollment->id,
            'academic_year_id' => $year->id,
            'branch_id' => $branch->id,
            'payment_date' => '2026-09-23',
            'currency' => 'USD',
            'tuition_amount' => '50.00',
            'administrative_fee' => '10.00',
            'payment_method' => 'cash',
        ]);

        $payment = Payment::first();
        $response->assertRedirect(route('payments.show', $payment));
        $this->assertNotNull($payment->invoice);
        $this->assertSame('60.00', $payment->total_amount);
        $this->assertEquals(60, $payment->journalEntry->lines()->sum('debit'));
        $this->assertEquals(60, $payment->journalEntry->lines()->sum('credit'));
    }

    public function test_payment_record_updates_enrollment_and_student_to_active_state(): void
    {
        $branch = Branch::create(['code' => 'ACT', 'name' => 'Active Branch', 'status' => 'active']);
        $user = User::factory()->create(['branch_id' => $branch->id, 'role' => 'super_admin']);
        $student = Student::create(['code' => 'STU-200', 'name_kh' => 'សិស្សថ្មី', 'gender' => 'F', 'status' => 'pending', 'branch_id' => $branch->id]);
        $year = AcademicYear::create(['name' => '2026-2027', 'start_date' => '2026-09-01', 'end_date' => '2027-08-31', 'is_active' => true, 'branch_id' => $branch->id]);
        $grade = Grade::create(['name' => 'Grade 1', 'level' => 1, 'branch_id' => $branch->id]);
        $class = SchoolClass::create(['name' => 'A', 'academic_year_id' => $year->id, 'grade_id' => $grade->id, 'branch_id' => $branch->id, 'capacity' => 30]);
        $enrollment = Enrollment::create(['student_id' => $student->id, 'branch_id' => $branch->id, 'academic_year_id' => $year->id, 'grade_id' => $grade->id, 'class_id' => $class->id, 'enrollment_date' => '2026-09-01', 'status' => 'active']);

        $response = $this->actingAs($user)->post(route('payments.store'), [
            'student_id' => $student->id,
            'enrollment_id' => $enrollment->id,
            'academic_year_id' => $year->id,
            'branch_id' => $branch->id,
            'payment_date' => '2026-09-23',
            'currency' => 'USD',
            'tuition_amount' => '50.00',
            'administrative_fee' => '10.00',
            'payment_method' => 'cash',
        ]);

        $payment = Payment::first();

        $response->assertRedirect(route('payments.show', $payment));
        $this->assertSame('active', $enrollment->fresh()->status);
        $this->assertSame('active', $student->fresh()->status);
        $this->assertNotNull($payment->enrollment_id);
    }

    public function test_expense_creates_balanced_journal_entry(): void
    {
        $branch = Branch::create(['code' => 'EXP', 'name' => 'Expense Branch', 'status' => 'active']);
        $user = User::factory()->create(['branch_id' => $branch->id, 'role' => 'super_admin']);

        $response = $this->actingAs($user)->post(route('expenses.store'), [
            'branch_id' => $branch->id,
            'expense_date' => '2026-09-23',
            'category' => 'Utilities',
            'description' => 'Electricity',
            'amount' => '25.50',
            'currency' => 'USD',
            'payment_method' => 'cash',
        ]);

        $expense = Expense::first();
        $response->assertRedirect(route('expenses.show', $expense));
        $this->assertSame('25.50', $expense->amount);
        $this->assertEquals(25.5, $expense->journalEntry->lines()->sum('debit'));
        $this->assertEquals(25.5, $expense->journalEntry->lines()->sum('credit'));
    }

    public function test_payment_records_months_discount_and_service_items(): void
    {
        $branch = Branch::create(['code' => 'MON', 'name' => 'Monthly Branch', 'status' => 'active']);
        $user = User::factory()->create(['branch_id' => $branch->id, 'role' => 'super_admin']);
        $student = Student::create(['code' => 'MON-001', 'name_kh' => 'សិស្សប្រចាំខែ', 'gender' => 'M', 'status' => 'pending', 'branch_id' => $branch->id]);
        $year = AcademicYear::create(['name' => '2026-2027', 'start_date' => '2026-09-01', 'end_date' => '2027-08-31', 'is_active' => true, 'branch_id' => $branch->id]);
        $grade = \App\Models\Grade::create(['name' => 'Grade 1', 'level' => 1, 'monthly_tuition_fee' => 20, 'branch_id' => $branch->id]);
        $class = \App\Models\SchoolClass::create(['name' => '1A', 'academic_year_id' => $year->id, 'grade_id' => $grade->id, 'capacity' => 30, 'monthly_tuition_fee' => 20, 'branch_id' => $branch->id]);
        $enrollment = \App\Models\Enrollment::create(['student_id' => $student->id, 'branch_id' => $branch->id, 'academic_year_id' => $year->id, 'grade_id' => $grade->id, 'class_id' => $class->id, 'enrollment_date' => '2026-09-01', 'status' => 'active']);

        $response = $this->actingAs($user)->post(route('payments.store'), [
            'student_id' => $student->id,
            'enrollment_id' => $enrollment->id,
            'academic_year_id' => $year->id,
            'branch_id' => $branch->id,
            'currency' => 'USD',
            'payment_date' => '2026-09-24',
            'tuition_amount' => 40,
            'administrative_fee' => 0,
            'selected_months' => ['2026-09', '2026-10'],
            'monthly_fee' => 20,
            'discount_type' => 'percent',
            'discount_amount' => 10,
            'line_items' => [['description' => 'សេវាដឹកជញ្ជូន', 'amount' => 10]],
            'payment_method' => 'cash',
        ]);

        $payment = Payment::first();
        $response->assertRedirect(route('payments.show', $payment));
        $this->assertSame('46.00', $payment->total_amount);
        $this->assertCount(2, $payment->paymentMonths);
        $this->assertTrue($payment->items->contains('description', 'សេវាដឹកជញ្ជូន'));
        $this->assertSame('មួយរយដប់ ដុល្លារអាមេរិកគត់', KhmerAmountService::money(110, 'USD'));
    }

    public function test_usd_payment_uses_database_service_and_tf_reference_without_branch_input(): void
    {
        $branch = Branch::create(['code' => 'TF', 'name' => 'TF Branch', 'status' => 'active']);
        $user = User::factory()->create(['branch_id' => $branch->id, 'role' => 'super_admin']);
        $student = Student::create(['code' => 'TF-001', 'name_kh' => 'សិស្ស TF', 'gender' => 'F', 'status' => 'pending', 'branch_id' => $branch->id]);
        $year = AcademicYear::create(['name' => '2026-2027', 'start_date' => '2026-09-01', 'end_date' => '2027-08-31', 'is_active' => true, 'branch_id' => $branch->id]);
        $grade = \App\Models\Grade::create(['name' => 'Grade 1', 'level' => 1, 'monthly_tuition_fee' => 30, 'branch_id' => $branch->id]);
        $class = \App\Models\SchoolClass::create(['name' => '1A', 'academic_year_id' => $year->id, 'grade_id' => $grade->id, 'capacity' => 30, 'monthly_tuition_fee' => 30, 'branch_id' => $branch->id]);
        $enrollment = \App\Models\Enrollment::create(['student_id' => $student->id, 'branch_id' => $branch->id, 'academic_year_id' => $year->id, 'grade_id' => $grade->id, 'class_id' => $class->id, 'enrollment_date' => '2026-09-01', 'status' => 'active']);
        $service = Service::create(['branch_id' => $branch->id, 'name_kh' => 'ថ្លៃដឹក', 'price' => 20, 'status' => 'active']);

        $response = $this->actingAs($user)->post(route('payments.store'), [
            'student_id' => $student->id,
            'enrollment_id' => $enrollment->id,
            'selected_months' => ['2026-09'],
            'monthly_fee' => 30,
            'administrative_fee' => 0,
            'service_id' => $service->id,
            'payment_date' => '2026-09-24',
            'payment_method' => 'cash',
        ]);

        $payment = Payment::first();
        $response->assertRedirect(route('payments.show', $payment));
        $this->assertSame('TF000001', $payment->reference);
        $this->assertSame('USD', $payment->currency);
        $this->assertSame('50.00', $payment->total_amount);
        $this->assertDatabaseHas('payment_items', ['payment_id' => $payment->id, 'description' => 'ថ្លៃដឹក', 'amount' => 20]);
    }
}
