<?php

namespace App\Http\Controllers;

use App\Models\JournalEntry;
use Illuminate\Http\Request;

class AccountingController extends Controller
{
    public function index(Request $request)
    {
        $query = JournalEntry::with(['lines.account', 'creator']);
        if (!$request->user()->isSuperAdmin()) $query->where('branch_id', $request->user()->branch_id);
        if ($search = $request->input('search')) $query->where(fn ($q) => $q->where('reference', 'like', "%{$search}%")->orWhere('description', 'like', "%{$search}%"));
        $entries = $query->latest('entry_date')->paginate(15)->withQueryString();
        return view('accounting.index', compact('entries'));
    }

    public function show(JournalEntry $accounting)
    {
        abort_unless(auth()->user()->isSuperAdmin() || auth()->user()->branch_id === $accounting->branch_id, 403);
        $accounting->load(['lines.account', 'creator']);
        return view('accounting.show', ['entry' => $accounting]);
    }
}
