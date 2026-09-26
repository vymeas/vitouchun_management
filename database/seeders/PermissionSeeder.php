<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'finance.view', 'finance.create', 'finance.edit', 'finance.delete', 'finance.approve', 'finance.print', 'finance.export',
            'payment.view', 'payment.create', 'payment.edit', 'payment.delete',
            'invoice.view', 'invoice.print',
            'expense.view', 'expense.create', 'expense.edit', 'expense.delete',
            'accounting.view', 'accounting.manage', 'reports.finance',
            'scores.view', 'scores.create', 'scores.edit', 'scores.delete', 'reports.scores',
            'attendance.view', 'attendance.create', 'attendance.edit', 'attendance.delete', 'reports.attendance',
            'student_cards.view', 'student_cards.create', 'student_cards.edit', 'student_cards.delete', 'student_cards.print',
            'reports.view', 'reports.export', 'reports.print',
            'employees.view', 'employees.create', 'employees.edit', 'employees.delete', 'employees.export', 'employees.print',
            'teachers.view', 'teachers.create', 'teachers.edit', 'teachers.delete', 'teacher_assignments.view', 'teacher_assignments.create', 'teacher_assignments.edit', 'teacher_assignments.delete', 'teacher_schedule.view', 'teacher_schedule.create', 'teacher_schedule.edit', 'teacher_schedule.delete',
        ];

        foreach ($permissions as $name) {
            [$module, $action] = explode('.', $name, 2);
            Permission::updateOrCreate(
                ['name' => $name],
                ['display_name' => ucfirst($action) . ' ' . ucfirst($module), 'module' => $module, 'action' => $action]
            );
        }

        $permissionIds = Permission::whereIn('name', $permissions)->pluck('id', 'name');
        foreach (['admin', 'accountant'] as $role) {
            foreach ($permissionIds as $permissionId) {
                DB::table('role_permissions')->updateOrInsert(
                    ['role' => $role, 'permission_id' => $permissionId],
                    ['updated_at' => now(), 'created_at' => now()]
                );
            }
        }
    }
}
