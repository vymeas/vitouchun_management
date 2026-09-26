@extends('layouts.app')
@section('title', 'ប្រតិបត្តិការគណនេយ្យ')
@section('page-title', 'ប្រតិបត្តិការគណនេយ្យ')
@section('content')
<div class="card mx-auto" style="max-width:850px"><div class="card-header"><h5 class="mb-0">{{ $entry->reference }}</h5></div><div class="card-body"><p><strong>{{ $entry->entry_date?->format('d/m/Y') }}</strong> · {{ $entry->description }}</p><table class="table"><thead><tr><th>គណនី</th><th class="text-end">Debit</th><th class="text-end">Credit</th></tr></thead><tbody>@foreach($entry->lines as $line)<tr><td>{{ $line->account?->code }} - {{ $line->account?->name }}</td><td class="text-end">{{ number_format($line->debit, 2) }}</td><td class="text-end">{{ number_format($line->credit, 2) }}</td></tr>@endforeach</tbody><tfoot class="fw-bold"><tr><td>សរុប</td><td class="text-end">{{ number_format($entry->lines->sum('debit'), 2) }}</td><td class="text-end">{{ number_format($entry->lines->sum('credit'), 2) }}</td></tr></tfoot></table><a href="{{ route('accounting.index') }}" class="btn btn-outline-secondary">ត្រឡប់</a></div></div>
@endsection
