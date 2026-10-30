<nav class="mb-6 flex flex-wrap gap-2 text-sm font-semibold">
    <a href="{{ route('setup.index') }}" class="subnav-link" aria-current="{{ request()->routeIs('setup.index', 'setup.years*', 'setup.year.terms*') ? 'page' : 'false' }}">Academic years</a>
    <a href="{{ route('setup.classes') }}" class="subnav-link" aria-current="{{ request()->routeIs('setup.classes*') ? 'page' : 'false' }}">Classes</a>
    <a href="{{ route('setup.subjects') }}" class="subnav-link" aria-current="{{ request()->routeIs('setup.subjects*') ? 'page' : 'false' }}">Subjects</a>
    <a href="{{ route('setup.assignments') }}" class="subnav-link" aria-current="{{ request()->routeIs('setup.assignments*') ? 'page' : 'false' }}">Assignments</a>
</nav>
