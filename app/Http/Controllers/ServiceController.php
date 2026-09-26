<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $branchId = $request->user()->isSuperAdmin() ? null : $request->user()->branch_id;
        $services = Service::forBranch($branchId)->with('branch')->orderBy('name_kh')->paginate(20);
        $branches = $request->user()->isSuperAdmin() ? Branch::active()->orderBy('name')->get() : collect();
        return view('services.index', compact('services', 'branches'));
    }

    public function update(Request $request, Service $service)
    {
        abort_unless($request->user()->isSuperAdmin() || $request->user()->branch_id === $service->branch_id, 403);
        $data = $request->validate([
            'price' => ['required', 'numeric', 'gte:0'],
            'status' => ['required', 'in:active,inactive'],
        ], [
            'price.required' => 'សូមបញ្ចូលតម្លៃសេវាកម្ម។',
            'price.gte' => 'តម្លៃសេវាកម្មមិនអាចអវិជ្ជមានទេ។',
        ]);
        $service->update($data);
        return redirect()->route('settings.services.index')->with('success', 'បានធ្វើបច្ចុប្បន្នភាពសេវាកម្មដោយជោគជ័យ។');
    }
}
