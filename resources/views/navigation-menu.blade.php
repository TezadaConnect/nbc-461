<nav class="navbar navbar-expand-md navbar-light bg-white border-bottom sticky-top">
    <div class="container">
        <!-- Logo -->
        <a class="navbar-brand mr-4" href="/dashboard" style="color:maroon">
            <img src="{{ asset('img/android-chrome-192x192.png') }}" width="36" class="mr-1">
            PUP E-QAR
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <!-- Left Side Of Navbar -->
            <ul class="navbar-nav mr-auto">
                <li>
                    <x-jet-nav-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-jet-nav-link>
                </li>
                
                @if (auth()->user()->role_id == 1)
                <li class="navbar-nav mr-auto">
                    <x-jet-nav-link href="{{ route('users.index') }}" :active="request()->routeIs('users.*')">
                        {{ __('Users') }}
                    </x-jet-nav-link>
                </li>
                
                <li class="navbar-nav mr-auto">
                    <x-jet-nav-link href="{{ route('maintenances.index') }}" :active="request()->routeIs('maintenances.*') || request()->routeIs('announcements.*') || request()->routeIs('dropdowns.*') || request()->routeIs('research-forms.*') || request()->routeIs('report-types.*') || request()->routeIs('report-categories.*')">
                        {{ __('Maintenances') }}
                    </x-jet-nav-link>
                </li>

                @elseif (auth()->user()->role_id == 3)
                <li class="navbar-nav mr-auto">
                    <x-jet-nav-link href="{{ route('research.index') }}" :active="request()->routeIs('research.*') || request()->routeIs('research-completed.*') || request()->routeIs('research-publication.*')|| request()->routeIs('research-presentation.*')|| request()->routeIs('research-citation.*') ||request()->routeIs('research-utilization.*') || request()->routeIs('research-copyrighted.*')">
                        {{ __('Research') }}
                    </x-jet-nav-link>
                </li>
                <li class="navbar-nav mr-auto">
                    <x-jet-nav-link href="{{ route('inventions.index') }}" :active="request()->routeIs('inventions.*')">
                        {{ __('Inventions') }}
                    </x-jet-nav-link>
                </li>
                <li class="navbar-nav mr-auto">
                    <x-jet-nav-link href="{{ route('faculty.index') }}" :active="request()->routeIs('faculty.*')">
                        {{ __('Reports') }}
                    </x-jet-nav-link>
                </li>
                @endif

            </ul>
            
            <!-- Right Side Of Navbar -->
            <ul class="navbar-nav ml-auto align-items-baseline">

                <!-- Settings Dropdown -->
                @auth
                    <x-jet-dropdown id="settingsDropdown">
                        <x-slot name="trigger">
                            @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                                <img class="rounded-circle" width="32" height="32" src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}" />
                            @else
                                {{ Auth::user()->first_name }}

                                <svg class="ml-2" width="18" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            @endif
                        </x-slot>

                        <x-slot name="content">
                            <!-- Account Management -->
                            <h6 class="dropdown-header small text-muted">
                                {{ __('Manage Account') }}
                            </h6>

                            <x-jet-dropdown-link href="{{ route('profile.show') }}">
                                {{ __('Profile') }}
                            </x-jet-dropdown-link>
                            <hr class="dropdown-divider">

                            <!-- Authentication -->
                            <x-jet-dropdown-link href="{{ route('logout') }}"
                                                 onclick="event.preventDefault();
                                                         document.getElementById('logout-form').submit();">
                                {{ __('Log out') }}
                            </x-jet-dropdown-link>
                            <form method="POST" id="logout-form" action="{{ route('logout') }}">
                                @csrf
                            </form>
                        </x-slot>
                    </x-jet-dropdown>
                @endauth
            </ul>
        </div>
    </div>
</nav>