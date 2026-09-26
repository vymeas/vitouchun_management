<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Http\Requests\BranchRequest;
use App\Services\AuditService;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function index(Request $request)
    {
        $query = Branch::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('name_kh', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $branches = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('branches.index', compact('branches'));
    }

    public function create()
    {
        return view('branches.create');
    }

    public function store(BranchRequest $request)
    {
        $branch = Branch::create($request->validated());

        AuditService::log('branch.created', "បង្កើតសាខា: {$branch->name}", $branch, [], $branch->toArray());

        return redirect()->route('branches.index')
            ->with('success', 'បានបង្កើតសាខាដោយជោគជ័យ។');
    }

    public function show(Branch $branch)
    {
        $branch->load(['users' => fn($q) => $q->active()]);
        return view('branches.show', compact('branch'));
    }

    public function edit(Branch $branch)
    {
        return view('branches.edit', compact('branch'));
    }

    public function update(BranchRequest $request, Branch $branch)
    {
        $old = $branch->toArray();
        $branch->update($request->validated());

        AuditService::log('branch.updated', "កែប្រែសាខា: {$branch->name}", $branch, $old, $branch->fresh()->toArray());

        return redirect()->route('branches.index')
            ->with('success', 'បានកែប្រែសាខាដោយជោគជ័យ។');
    }

    public function destroy(Branch $branch)
    {
        // Prevent deleting branch that has users
        if ($branch->users()->count() > 0) {
            return back()->with('error', 'មិនអាចលុបសាខានេះបានទេ ព្រោះមានអ្នកប្រើប្រាស់ភ្ជាប់ជាមួយ។');
        }

        AuditService::log('branch.deleted', "លុបសាខា: {$branch->name}", $branch, $branch->toArray());
        $branch->delete();

        return redirect()->route('branches.index')
            ->with('success', 'បានលុបសាខាដោយជោគជ័យ។');
    }
}
