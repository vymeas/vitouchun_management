<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Branch;
use App\Models\Permission;
use App\Http\Requests\UserRequest;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = User::with('branch')->where('is_deleted', false);

        // Branch isolation: non-super-admin only sees their branch
        if (!$user->isSuperAdmin()) {
            $query->where('branch_id', $user->branch_id);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('full_name_kh', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($role = $request->input('role')) {
            $query->where('role', $role);
        }

        if ($branchId = $request->input('branch_id')) {
            $query->where('branch_id', $branchId);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $users = $query->orderBy('full_name')->paginate(20)->withQueryString();
        $branches = $user->isSuperAdmin() ? Branch::active()->orderBy('name')->get() : collect();
        $roles = $this->getAvailableRoles($user);

        return view('users.index', compact('users', 'branches', 'roles'));
    }

    public function create()
    {
        $currentUser = auth()->user();
        $branches = $currentUser->isSuperAdmin()
            ? Branch::active()->orderBy('name')->get()
            : Branch::where('id', $currentUser->branch_id)->get();

        $roles = $this->getAvailableRoles($currentUser);

        return view('users.create', compact('branches', 'roles'));
    }

    public function store(UserRequest $request)
    {
        $currentUser = auth()->user();
        $data = $request->validated();

        // Branch-scope: non-super-admin can only create users in their branch
        if (!$currentUser->isSuperAdmin()) {
            $data['branch_id'] = $currentUser->branch_id;
        }

        $user = User::create($data);

        AuditService::log('user.created', "បង្កើតអ្នកប្រើ: {$user->full_name}", $user, [], array_diff_key($user->toArray(), ['password' => '']));

        return redirect()->route('users.index')
            ->with('success', 'បានបង្កើតអ្នកប្រើដោយជោគជ័យ។');
    }

    public function show(User $user)
    {
        $this->authorizeUserAccess($user);
        $user->load('branch', 'permissions');
        $allPermissions = Permission::orderBy('module')->orderBy('action')->get()->groupBy('module');

        return view('users.show', compact('user', 'allPermissions'));
    }

    public function edit(User $user)
    {
        $this->authorizeUserAccess($user);
        $currentUser = auth()->user();

        $branches = $currentUser->isSuperAdmin()
            ? Branch::active()->orderBy('name')->get()
            : Branch::where('id', $currentUser->branch_id)->get();

        $roles = $this->getAvailableRoles($currentUser);

        return view('users.edit', compact('user', 'branches', 'roles'));
    }

    public function update(UserRequest $request, User $user)
    {
        $this->authorizeUserAccess($user);

        $old = $user->toArray();
        $data = $request->validated();

        // Don't update password if blank
        if (empty($data['password'])) {
            unset($data['password']);
        }

        $user->update($data);
        $user->clearPermissionCache();

        AuditService::log('user.updated', "កែប្រែអ្នកប្រើ: {$user->full_name}", $user, $old, $user->fresh()->toArray());

        return redirect()->route('users.index')
            ->with('success', 'បានកែប្រែអ្នកប្រើដោយជោគជ័យ។');
    }

    public function destroy(User $user)
    {
        $this->authorizeUserAccess($user);

        // Prevent self-deletion
        if ($user->id === auth()->id()) {
            return back()->with('error', 'មិនអាចលុបគណនីខ្លួនឯងបានទេ។');
        }

        $user->update(['is_deleted' => true, 'status' => 'inactive']);
        $user->clearPermissionCache();

        AuditService::log('user.deleted', "លុបអ្នកប្រើ: {$user->full_name}", $user);

        return redirect()->route('users.index')
            ->with('success', 'បានលុបអ្នកប្រើដោយជោគជ័យ។');
    }

    /**
     * Manage user-specific permissions
     */
    public function permissions(User $user)
    {
        $this->authorizeUserAccess($user);
        $allPermissions = Permission::orderBy('module')->orderBy('action')->get()->groupBy('module');
        $userPermissions = $user->permissions()->pluck('permissions.id')->toArray();

        return view('users.permissions', compact('user', 'allPermissions', 'userPermissions'));
    }

    /**
     * Save user-specific permissions
     */
    public function savePermissions(Request $request, User $user)
    {
        $this->authorizeUserAccess($user);

        $permissions = $request->input('permissions', []);
        $sync = [];
        foreach ($permissions as $permId => $granted) {
            $sync[$permId] = ['granted' => (bool) $granted];
        }

        $user->permissions()->sync($sync);
        $user->clearPermissionCache();

        AuditService::log('user.permissions_changed', "ផ្លាស់ប្ដូរ permission: {$user->full_name}", $user);

        return redirect()->route('users.show', $user)
            ->with('success', 'បានកំណត់ permission ដោយជោគជ័យ។');
    }

    // ==================== Private Helpers ====================

    /**
     * Ensure the current user can access the target user (branch isolation)
     */
    private function authorizeUserAccess(User $user): void
    {
        $currentUser = auth()->user();
        if (!$currentUser->isSuperAdmin() && $user->branch_id !== $currentUser->branch_id) {
            abort(403, 'អ្នកមិនមានសិទ្ធិចូលប្រើទិន្នន័យនេះទេ។');
        }
    }

    /**
     * Get roles available to the current user to assign
     */
    private function getAvailableRoles(User $currentUser): array
    {
        $all = [
            'super_admin' => 'Super Admin',
            'admin'       => 'Admin',
            'accountant'  => 'គណនេយ្យ',
            'registrar'   => 'ការិយាល័យ',
            'teacher'     => 'គ្រូ',
            'staff'       => 'បុគ្គលិក',
        ];

        if (!$currentUser->isSuperAdmin()) {
            unset($all['super_admin']);
        }

        return $all;
    }
}
