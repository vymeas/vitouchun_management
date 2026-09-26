<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGradeRequest;
use App\Http\Requests\UpdateGradeRequest;
use App\Models\Branch;
use App\Models\Grade;
use App\Services\AuditService;
use Illuminate\Http\Request;

class GradeController extends Controller
{
    public function index(Request $request)
    {
        $query = Grade::query()->with('branch');

        if (!$request->user()->isSuperAdmin()) {
            $query->where('branch_id', $request->user()->branch_id);
        }

        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        $grades = $query->orderBy('level')->paginate(15)->withQueryString();
        $branches = $request->user()->isSuperAdmin() ? Branch::active()->orderBy('name')->get() : collect();

        return view('grades.index', compact('grades', 'branches'));
    }

    public function create()
    {
        $branches = auth()->user()->isSuperAdmin()
            ? Branch::active()->orderBy('name')->get()
            : Branch::where('id', auth()->user()->branch_id)->get();

        return view('grades.create', compact('branches'));
    }

    public function store(StoreGradeRequest $request)
    {
        $data = $request->validated();

        if (!$request->user()->isSuperAdmin()) {
            $data['branch_id'] = $request->user()->branch_id;
        }

        $grade = Grade::create($data);
        AuditService::log('grade.created', 'បង្កើតថ្នាក់: ' . $grade->name, $grade, [], $grade->toArray());

        return redirect()->route('grades.index')->with('success', 'បានបង្កើតថ្នាក់រៀនដោយជោគជ័យ។');
    }

    public function show(Grade $grade)
    {
        $grade->load('branch');

        return view('grades.show', compact('grade'));
    }

    public function edit(Grade $grade)
    {
        $branches = auth()->user()->isSuperAdmin()
            ? Branch::active()->orderBy('name')->get()
            : Branch::where('id', auth()->user()->branch_id)->get();

        return view('grades.edit', compact('grade', 'branches'));
    }

    public function update(UpdateGradeRequest $request, Grade $grade)
    {
        $data = $request->validated();

        if (!$request->user()->isSuperAdmin()) {
            $data['branch_id'] = $request->user()->branch_id;
        }

        $old = $grade->toArray();
        $grade->update($data);
        AuditService::log('grade.updated', 'កែប្រែថ្នាក់: ' . $grade->name, $grade, $old, $grade->fresh()->toArray());

        return redirect()->route('grades.index')->with('success', 'បានកែប្រែថ្នាក់រៀនដោយជោគជ័យ។');
    }

    public function destroy(Grade $grade)
    {
        $grade->delete();
        AuditService::log('grade.deleted', 'លុបថ្នាក់: ' . $grade->name, $grade, $grade->toArray());

        return redirect()->route('grades.index')->with('success', 'បានលុបថ្នាក់រៀនដោយជោគជ័យ។');
    }
}
