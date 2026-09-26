@extends('layouts.app')
@section('title','ផ្ទាំងបុគ្គលិក')
@section('page-title','ផ្ទាំងបុគ្គលិក')
@section('content')
<div class="page-header"><div><h2 class="page-header-title"><i class="fas fa-users text-primary me-2"></i>ផ្ទាំងបុគ្គលិក</h2><p class="page-header-subtitle">ស្ថិតិពីទិន្នន័យបុគ្គលិកពិត</p></div><a href="{{ route('employees.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i>បង្កើតបុគ្គលិក</a></div><div class="row g-3">@foreach([['បុគ្គលិកសរុប',$total,'primary'],['កំពុងធ្វើការ',$active,'success'],['ឈប់/បិទ',$inactive,'danger'],['កំពុងសុំច្បាប់',$leave,'warning'],['ប្រុស',$male,'info'],['ស្រី',$female,'secondary']] as [$label,$value,$color])<div class="col-md-4 col-lg-2"><div class="card p-3"><span class="text-muted">{{ $label }}</span><strong class="fs-3 text-{{ $color }}">{{ number_format($value) }}</strong></div></div>@endforeach</div><div class="card mt-4 p-3"><a href="{{ route('employees.index') }}" class="btn btn-outline-primary">មើលបញ្ជីបុគ្គលិក</a></div>
@endsection
