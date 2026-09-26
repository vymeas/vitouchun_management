<?php

namespace App\Services;

use App\Models\Enrollment;
use App\Models\Student;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class PaymentEligibilityService
{
    public function resolve(User $user, Student $student, ?int $enrollmentId = null): Enrollment
    {
        if (!$user->isSuperAdmin() && (int) $user->branch_id !== (int) $student->branch_id) {
            abort(403, 'សិស្សនេះមិនស្ថិតក្នុងសាខារបស់អ្នកទេ។');
        }

        $query = Enrollment::with(['student', 'academicYear', 'grade', 'schoolClass.teacher'])
            ->where('student_id', $student->id)
            ->where('branch_id', $student->branch_id)
            ->where('status', 'active')
            ->whereNotNull('academic_year_id')
            ->whereNotNull('grade_id')
            ->whereNotNull('class_id');

        if ($enrollmentId) {
            $query->whereKey($enrollmentId);
        }

        $enrollment = $query->latest('enrollment_date')->first();
        if (!$enrollment) {
            throw ValidationException::withMessages(['student_id' => 'សិស្សនេះមិនទាន់មានការចុះឈ្មោះសកម្មទេ។ សូមចុះឈ្មោះសិស្សជាមុនសិន មុនពេលទទួលប្រាក់។']);
        }

        return $enrollment;
    }
}
