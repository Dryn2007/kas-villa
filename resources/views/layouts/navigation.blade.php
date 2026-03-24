<nav x-data="{ open: false }" class="bg-white border-b border-gray-100 shadow-sm sticky top-0 z-40">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center">
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}"
                        class="flex items-center gap-3 group transition-transform active:scale-95">
                        <img src="{{ asset('images/logo.png') }}" alt="Keluarga H Solichin"
                            class="h-10 w-10 rounded-full shadow-sm group-hover:shadow-md transition-all object-cover">
                        <span class="font-extrabold text-lg tracking-tight text-gray-800">
                            Keluarga <span class="text-teal-600">H Solichin</span>
                        </span>
                    </a>
                </div>

            </div>

            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button
                            class="inline-flex items-center px-2 py-1.5 border border-transparent text-sm leading-4 font-medium rounded-full text-gray-500 bg-gray-50 hover:bg-gray-100 hover:text-gray-700 focus:outline-none transition ease-in-out duration-150 shadow-sm border-gray-200">
                            <div class="flex items-center gap-2 pr-1">
                                <img src="{{ Auth::user()->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->name) . '&color=0D9488&background=F0FDFA' }}"
                                    alt="Profile"
                                    class="h-8 w-8 rounded-full object-cover border-2 border-white shadow-sm">
                                <span class="font-bold text-gray-700">{{ Auth::user()->name }}</span>
                            </div>

                            <div class="ms-1 pr-2">
                                <svg class="fill-current h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <div class="px-4 py-3 border-b border-gray-100 bg-gray-50 rounded-t-md">
                            <p class="text-xs text-gray-500 font-medium">Login sebagai</p>
                            <p class="text-sm font-extrabold text-gray-800 truncate">{{ Auth::user()->email }}</p>
                        </div>

                        <x-dropdown-link :href="route('profile.edit')"
                            class="flex items-center gap-2 hover:bg-teal-50 hover:text-teal-700 font-semibold py-2.5 transition-colors">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            {{ __('Profile Saya') }}
                        </x-dropdown-link>

                        @if(Auth::user()->role === 'admin')
                            <x-dropdown-link :href="route('admin.index')"
                                class="flex items-center gap-2 hover:bg-yellow-50 hover:text-yellow-700 font-semibold py-2.5 transition-colors border-b border-gray-50">
                                <svg class="w-4 h-4 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z">
                                    </path>
                                </svg>
                                {{ __('Panel Admin') }}
                            </x-dropdown-link>
                        @endif

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault();
                                                this.closest('form').submit();"
                                class="flex items-center gap-2 text-red-600 hover:bg-red-50 hover:text-red-700 font-semibold py-2.5 transition-colors border-t border-gray-50">
                                <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                                    </path>
                                </svg>
                                {{ __('Keluar') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Mobile Profile Dropdown -->
            <div class="flex items-center sm:hidden" x-data="{ mobileOpen: false }">
                <button @click="mobileOpen = !mobileOpen"
                    class="inline-flex items-center justify-center p-1 rounded-full hover:bg-gray-100 focus:outline-none transition">
                    <img src="{{ Auth::user()->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->name) . '&color=0D9488&background=F0FDFA' }}"
                        alt="Profile" class="h-10 w-10 rounded-full object-cover border-2 border-teal-100 shadow-sm">
                </button>

                <!-- Dropdown Menu Mobile (Kecil di Pojok Kanan) -->
                <div x-show="mobileOpen" @click.away="mobileOpen = false" x-transition
                    class="absolute right-4 top-16 bg-white rounded-2xl shadow-xl border border-gray-200 w-48 z-50">
                    <div class="px-4 py-3 border-b border-gray-100 bg-gray-50 rounded-t-xl">
                        <p class="text-xs text-gray-500 font-medium">Login sebagai</p>
                        <p class="text-sm font-extrabold text-gray-800 truncate">{{ Auth::user()->name }}</p>
                        <p class="text-xs text-gray-500 truncate">{{ Auth::user()->email }}</p>
                    </div>

                    <div class="px-2 py-2 space-y-1">
                        <a href="{{ route('profile.edit') }}"
                            class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-teal-50 text-gray-700 hover:text-teal-700 font-semibold text-sm transition-colors">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            Profile Saya
                        </a>

                        @if(Auth::user()->role === 'admin')
                            <a href="{{ route('admin.index') }}"
                                class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-yellow-50 text-gray-700 hover:text-yellow-700 font-semibold text-sm transition-colors">
                                <svg class="w-4 h-4 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z">
                                    </path>
                                </svg>
                                Panel Admin
                            </a>
                        @endif

                        <form method="POST" action="{{ route('logout') }}" class="border-t border-gray-100 pt-1">
                            @csrf
                            <button type="submit"
                                class="w-full flex items-center gap-2 px-3 py-2 rounded-lg text-red-600 hover:bg-red-50 hover:text-red-700 font-semibold text-sm transition-colors">
                                <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                                    </path>
                                </svg>
                                Keluar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>