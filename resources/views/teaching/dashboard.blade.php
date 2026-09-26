@extends('layouts.app')
@section('title','ផ្ទាំងគ្រូបង្រៀន')
@section('page-title','ផ្ទាំងគ្រូបង្រៀន')
@section('content')
<div class="page-header"><div><h2 class="page-header-title"><i class="fas fa-chalkboard-teacher text-primary me-2"></i>ផ្ទាំងគ្រូបង្រៀន</h2></div><div class="d-flex gap-2"><a href="{{ route('teacher-assignments.create') }}" class="btn btn-primary">ចាត់តាំងថ្នាក់</a><a href="{{ route('teacher-schedules.create') }}" class="btn btn-outline-primary">បង្កើតកាលវិភាគ</a></div></div><div class="row g-3">@foreach([['គ្រូ',$teachers],['ថ្នាក់មានគ្រូ',$classes],['មុខវិជ្ជា',$subjects],['សិស្ស',$students],['កាលវិភាគ',$schedules]] as [$label,$value])<div class="col-md"><div class="card p-3"><span class="text-muted">{{ $label }}</span><strong class="fs-3">{{ number_format($value) }}</strong></div></div>@endforeach</div><div class="card mt-4 p-3 d-flex flex-row gap-2"><a href="{{ route('teachers.index') }}" class="btn btn-outline-secondary">បញ្ជីគ្រូ</a><a href="{{ route('teacher-assignments.index') }}" class="btn btn-outline-secondary">Assignments</a><a href="{{ route('teacher-schedules.index') }}" class="btn btn-outline-secondary">កាលវិភាគ</a></div>
@endsection
