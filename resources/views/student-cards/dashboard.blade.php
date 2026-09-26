@extends('layouts.app')
@section('title', 'ផ្ទាំងកាតសិស្ស')
@section('page-title', 'ផ្ទាំងកាតសិស្ស')
@section('content')
<div class="page-header"><div><h2 class="page-header-title"><i class="fas fa-id-card text-primary me-2"></i>ផ្ទាំងកាតសិស្ស</h2><p class="page-header-subtitle">ស្ថិតិពីទិន្នន័យកាតពិត</p></div><a href="{{ route('digital-cards.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i>បង្កើតកាត</a></div><div class="row g-3"><div class="col-md-3"><div class="card p-3">សិស្សសកម្ម<strong class="fs-3">{{ $students }}</strong></div></div><div class="col-md-3"><div class="card p-3">កាតសកម្ម<strong class="fs-3 text-success">{{ $active }}</strong></div></div><div class="col-md-3"><div class="card p-3">កាតផុតកំណត់<strong class="fs-3 text-warning">{{ $expired }}</strong></div></div><div class="col-md-3"><div class="card p-3">កាតបិទ/បាត់<strong class="fs-3 text-danger">{{ $inactive }}</strong></div></div></div><div class="card mt-4 p-3"><strong>ជិតផុតកំណត់ក្នុង 30 ថ្ងៃ: {{ $expiring }}</strong> <a href="{{ route('digital-cards.index', ['status'=>'active']) }}" class="ms-2">មើលកាត</a></div>
@endsection
