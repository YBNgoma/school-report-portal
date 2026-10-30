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
        <div class="flex min-h-screen" data-shell data-sidebar-open="true">
            <aside id="sidebar" class="app-sidebar fixed inset-y-0 left-0 z-40 flex w-64 flex-col bg-[#0369a1] text-white">
                <div class="flex items-center gap-3 px-5 pt-6 pb-5">
                    <span class="grid h-9 w-9 place-items-center rounded-xl bg-white text-[#0369a1] font-display text-lg font-bold">S</span>
                    <span class="sidebar-label font-display text-lg leading-tight">School<br>Report Portal</span>
                </div>

                <nav class="flex-1 space-y-1 overflow-y-auto px-3 text-sm" aria-label="Main">
                    <a href="{{ route('dashboard') }}" class="sidebar-link" aria-current="{{ request()->routeIs('dashboard') ? 'page' : 'false' }}">
                        <span class="dot"></span><span class="sidebar-label">Overview</span>
                    </a>

                    @can(\App\Support\Permissions::MARKS_VIEW)
                        <a href="{{ route('marks.index') }}" class="sidebar-link" aria-current="{{ request()->routeIs('marks.*') ? 'page' : 'false' }}">
                            <span class="dot"></span><span class="sidebar-label">Marks</span>
                        </a>
                    @endcan

                    @canany([\App\Support\Permissions::REPORTS_COMPILE, \App\Support\Permissions::REPORTS_APPROVE, \App\Support\Permissions::REPORTS_PUBLISH])
                        <a href="{{ route('reports.index') }}" class="sidebar-link" aria-current="{{ request()->routeIs('reports.*') ? 'page' : 'false' }}">
                            <span class="dot"></span><span class="sidebar-label">Reports</span>
                        </a>
                    @endcanany

                    @can(\App\Support\Permissions::PORTAL_REPORTS_VIEW)
                        <a href="{{ route('portal.index') }}" class="sidebar-link" aria-current="{{ request()->routeIs('portal.*') ? 'page' : 'false' }}">
                            <span class="dot"></span><span class="sidebar-label">My children</span>
                        </a>
                    @endcan

                    @canany([\App\Support\Permissions::STUDENTS_MANAGE, \App\Support\Permissions::STUDENTS_ENROL])
                        <a href="{{ route('students.index') }}" class="sidebar-link" aria-current="{{ request()->routeIs('students.*') ? 'page' : 'false' }}">
                            <span class="dot"></span>                            <span class="sidebar-label">Students</span>
                        </a>
                    @endcanany

                    @can(\App\Support\Permissions::GUARDIANS_MANAGE)
                        <a href="{{ route('guardians.index') }}" class="sidebar-link" aria-current="{{ request()->routeIs('guardians.*') ? 'page' : 'false' }}">
                            <span class="dot"></span><span class="sidebar-label">Guardians</span>
                        </a>
                    @endcan

                    @can(\App\Support\Permissions::USERS_MANAGE)
                        <a href="{{ route('users.index') }}" class="sidebar-link" aria-current="{{ request()->routeIs('users.*') ? 'page' : 'false' }}">
                            <span class="dot"></span><span class="sidebar-label">Users</span>
                        </a>
                    @endcan

                    @can(\App\Support\Permissions::AUDIT_LOG)
                        <a href="{{ route('audit.index') }}" class="sidebar-link" aria-current="{{ request()->routeIs('audit.*') ? 'page' : 'false' }}">
                            <span class="dot"></span><span class="sidebar-label">Audit log</span>
                        </a>
                    @endcan

                    @can(\App\Support\Permissions::SETUP_MANAGE)
                        <a href="{{ route('setup.index') }}" class="sidebar-link" aria-current="{{ request()->routeIs('setup.*') ? 'page' : 'false' }}">
                            <span class="dot"></span><span class="sidebar-label">Setup</span>
                        </a>
                    @endcan

                    @can(\App\Support\Permissions::SETUP_MANAGE)
                        <a href="{{ route('settings.report-template') }}" class="sidebar-link" aria-current="{{ request()->routeIs('settings.*') ? 'page' : 'false' }}">
                            <span class="dot"></span><span class="sidebar-label">Report template</span>
                        </a>
                    @endcan
                </nav>

                <div class="border-t border-white/10 p-4">
                    <p class="sidebar-label truncate text-sm font-semibold">{{ auth()->user()->name }}</p>
                    <form method="POST" action="{{ route('logout') }}" class="mt-2">
                        @csrf
                        <button type="submit" class="text-xs text-white/60 underline underline-offset-4 hover:text-white">Sign out</button>
                    </form>
                </div>
            </aside>

            <div data-sidebar-backdrop class="app-backdrop fixed inset-0 z-30 bg-[#0c4a6e]/40 lg:hidden"></div>

            <div class="app-content flex min-w-0 flex-1 flex-col">
                <header class="sticky top-0 z-20 flex items-center gap-4 border-b border-[#e0ecf5] bg-[#f0f9ff]/90 px-6 py-4 backdrop-blur">
                    <button type="button" data-sidebar-toggle aria-controls="sidebar" aria-expanded="true"
                        class="icon-btn grid h-9 w-9 place-items-center rounded-lg"
                        aria-label="Toggle navigation">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"><path d="M2 4h12M2 8h12M2 12h12"/></svg>
                    </button>
                    <p class="font-display text-lg">@yield('title', 'Overview')</p>
                    <span class="ml-auto hidden text-sm text-[#4b6478] sm:block">{{ now()->format('l, j F Y') }}</span>
                </header>

                <main class="mx-auto w-full max-w-6xl flex-1 px-6 py-8">
                    @if (session('status'))
                        <div class="mb-6 rounded-xl border border-[#bae6fd] bg-[#e0f2fe] px-4 py-3 text-sm text-[#0369a1]">
                            {{ session('status') }}
                        </div>
                    @endif

                    @yield('content')
                </main>
            </div>
        </div>
    </body>
</html>
