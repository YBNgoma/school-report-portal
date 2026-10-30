<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>School Report Portal</title>

        @fonts

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-[#f0f9ff] text-[#0c4a6e] antialiased">
        <div class="mx-auto flex min-h-screen max-w-5xl flex-col px-6 py-8">
            <header class="flex items-center gap-3">
                <span class="grid h-9 w-9 place-items-center rounded-xl bg-[#0ea5e9] font-display text-lg font-bold text-white">S</span>
                <span class="font-display text-lg">School Report Portal</span>

                <nav class="ml-auto flex items-center gap-3 text-sm">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="rounded-lg bg-[#0c4a6e] px-4 py-2 text-white hover:bg-[#0369a1]">Open portal</a>
                    @else
                        <a href="{{ route('login') }}" class="rounded-lg bg-[#0c4a6e] px-4 py-2 text-white hover:bg-[#0369a1]">Sign in</a>
                    @endauth
                </nav>
            </header>

            <main class="grid flex-1 items-center gap-10 py-16 lg:grid-cols-2">
                <section>
                    <p class="text-xs font-semibold uppercase tracking-widest text-[#0369a1]">Term reporting, simplified</p>
                    <h1 class="font-display mt-3 text-5xl leading-tight">Marks in, reports out.</h1>
                    <p class="mt-5 max-w-md text-[#4b6478]">
                        Record marks by class and subject, compile term reports, approve them, and publish them to parents in one place.
                    </p>
                    <div class="mt-8 flex gap-3">
                        @auth
                            <a href="{{ url('/dashboard') }}" class="rounded-lg bg-[#0ea5e9] px-5 py-2.5 text-sm font-semibold text-white hover:bg-[#0284c7]">Go to dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="rounded-lg bg-[#0ea5e9] px-5 py-2.5 text-sm font-semibold text-white hover:bg-[#0284c7]">Sign in</a>
                        @endauth
                    </div>
                </section>

                <section class="paper-card grid gap-4 p-6">
                    <div class="rounded-xl bg-[#e0f2fe] p-5">
                        <p class="text-xs uppercase tracking-widest text-[#0369a1]">Published to parents</p>
                        <p class="font-display mt-1 text-4xl">Every term</p>
                    </div>
                    <div class="grid grid-cols-3 gap-3 text-center text-sm">
                        <div class="rounded-xl border border-[#e0ecf5] p-4"><p class="font-display text-2xl">Draft</p><p class="text-[#4b6478]">Compile</p></div>
                        <div class="rounded-xl border border-[#e0ecf5] p-4"><p class="font-display text-2xl">Review</p><p class="text-[#4b6478]">Approve</p></div>
                        <div class="rounded-xl border border-[#e0ecf5] p-4"><p class="font-display text-2xl">Live</p><p class="text-[#4b6478]">Publish</p></div>
                    </div>
                </section>
            </main>
        </div>
    </body>
</html>
