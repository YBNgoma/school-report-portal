<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', 'School Report Portal') · School Report Portal</title>

        {!! \Illuminate\Support\Facades\Vite::fonts() !!}

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-[#f0f9ff] text-[#0c4a6e] antialiased">
        <div class="mx-auto grid min-h-screen max-w-5xl items-center gap-10 px-6 py-10 lg:grid-cols-2">
            <div class="hidden lg:block">
                <a href="{{ url('/') }}" class="flex items-center gap-3">
                    <span class="grid h-9 w-9 place-items-center rounded-xl bg-[#0ea5e9] font-display text-lg font-bold text-white">S</span>
                    <span class="font-display text-lg">School Report Portal</span>
                </a>
                <p class="font-display mt-10 text-4xl leading-tight">Every mark, every term, in one place.</p>
                <p class="mt-4 max-w-sm text-[#4b6478]">Sign in to record marks, compile reports and share them with parents.</p>
            </div>

            <main class="paper-card w-full max-w-md justify-self-center p-8">
                <p class="lg:hidden mb-6 font-display text-lg">School Report Portal</p>
                <h1 class="font-display text-2xl">@yield('heading')</h1>
                @hasSection('subheading')
                    <p class="mt-1 mb-6 text-sm text-[#4b6478]">@yield('subheading')</p>
                @endif

                @if (session('status'))
                    <div class="mb-4 rounded-lg border border-[#bae6fd] bg-[#e0f2fe] px-4 py-2 text-sm text-[#0369a1]">
                        {{ session('status') }}
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </body>
</html>
