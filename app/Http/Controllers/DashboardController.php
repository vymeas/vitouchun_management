<?php

namespace App\Http\Controllers;

use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $studentQuery = Student::query();
        $classQuery = SchoolClass::query();

        if ($user && !$user->isSuperAdmin()) {
            $studentQuery->where('branch_id', $user->branch_id);
            $classQuery->where('branch_id', $user->branch_id);
        }

        $stats = [
            'total_students'    => (clone $studentQuery)->count(),
            'active_students'   => (clone $studentQuery)->where('status', 'active')->count(),
            'total_classes'     => (clone $classQuery)->count(),
            'total_staff'       => User::query()
                ->when($user && !$user->isSuperAdmin(), fn($query) => $query->where('branch_id', $user->branch_id))
                ->where('is_deleted', false)
                ->where('role', '!=', 'super_admin')
                ->count(),
            'today_payment'     => 0,
            'monthly_revenue'   => 0,
            'outstanding'       => 0,
        ];

        return view('dashboard.index', compact('stats'));
    }
}
