@extends('layouts.app')

@section('title', 'Audit log')

@section('content')
<p class="page-lead">The 200 most recent recorded actions. Read-only.</p>

<div class="paper-card overflow-x-auto p-6">
    <table class="tbl">
        <thead>
            <tr>
                <th>When</th>
                <th>User</th>
                <th>Action</th>
                <th>Details</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($logs as $log)
                <tr>
                    <td class="whitespace-nowrap text-[#5a7085]">{{ $log->created_at->format('j M Y H:i') }}</td>
                    <td>{{ $log->user?->name ?? 'System' }}</td>
                    <td><code class="font-mono text-xs">{{ $log->action }}</code></td>
                    <td>{{ $log->description }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="py-4 text-[#5a7085]">No actions recorded yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
