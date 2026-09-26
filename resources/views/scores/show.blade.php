@extends('layouts.app')
@section('title', 'ព័ត៌មានពិន្ទុ')
@section('page-title', 'ព័ត៌មានពិន្ទុ')
@section('content')
<div class="card mx-auto" style="max-width:760px"><div class="card-header"><h5 class="mb-0"><i class="fas fa-star-half-alt me-2"></i>ព័ត៌មានពិន្ទុ</h5></div><div class="card-body"><div class="row g-3"><div class="col-md-6"><strong>សិស្ស:</strong> {{ $score->student?->name_kh }} ({{ $score->student?->code }})</div><div class="col-md-6"><strong>មុខវិជ្ជា:</strong> {{ $score->subject?->name_kh }}</div><div class="col-md-6"><strong>ថ្នាក់:</strong> {{ $score->schoolClass?->name }}</div><div class="col-md-6"><strong>Term:</strong> {{ $score->term?->name }}</div><div class="col-md-6"><strong>ពិន្ទុ:</strong> {{ number_format($score->score, 2) }} / {{ number_format($score->maximum_score, 2) }}</div><div class="col-md-6"><strong>Grade:</strong> {{ $score->grade }}</div><div class="col-md-6"><strong>លទ្ធផល:</strong> {{ $score->result === 'passed' ? 'ជាប់' : 'ធ្លាក់' }}</div></div><div class="text-end mt-4"><a href="{{ route('scores.index') }}" class="btn btn-outline-secondary">ត្រឡប់</a></div></div></div>
@endsection
