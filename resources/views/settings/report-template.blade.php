@extends('layouts.app')

@section('title', 'Report template')

@section('content')
<p class="page-lead">Choose which sections appear on every report card PDF.</p>

<form method="POST" action="{{ route('settings.report-template.update') }}" class="paper-card max-w-xl space-y-4 p-6">
    @csrf
    @method('PUT')

    @foreach ($sections as $key => $label)
        <label class="flex items-center gap-3 text-sm">
            <input type="checkbox" name="sections[]" value="{{ $key }}" class="h-4 w-4 accent-[#0369a1]"
                @checked(in_array($key, $template->sections ?? [], true))>
            {{ $label }}
        </label>
    @endforeach

    <div class="flex justify-end pt-2">
        <button type="submit" class="btn">Save template</button>
    </div>
</form>
@endsection
