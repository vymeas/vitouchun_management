@extends('layouts.app')
@section('title', 'ព័ត៌មានចំណាយ')
@section('page-title', 'ព័ត៌មានចំណាយ')
@section('content')
<div class="card mx-auto" style="max-width:760px"><div class="card-header"><h5 class="mb-0">{{ $expense->reference }}</h5></div><div class="card-body"><div class="row g-3"><div class="col-md-6"><strong>កាលបរិច្ឆេទ:</strong> {{ $expense->expense_date?->format('d/m/Y') }}</div><div class="col-md-6"><strong>ប្រភេទ:</strong> {{ $expense->category }}</div><div class="col-md-6"><strong>ចំនួន:</strong> {{ $expense->currency }} {{ number_format($expense->amount, 2) }}</div><div class="col-md-6"><strong>វិធីបង់:</strong> {{ ucfirst($expense->payment_method) }}</div><div class="col-12"><strong>បរិយាយ:</strong> {{ $expense->description }}</div><div class="col-12"><strong>Journal:</strong> {{ $expense->journalEntry?->reference }}</div></div><div class="text-end mt-4"><a href="{{ route('expenses.index') }}" class="btn btn-outline-secondary">ត្រឡប់</a></div></div></div>
@endsection
