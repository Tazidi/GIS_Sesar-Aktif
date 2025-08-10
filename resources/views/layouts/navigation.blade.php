{{-- State 'searchOpen' dihapus karena sudah tidak relevan --}}
<nav x-data="{ open: false }" class="bg-white border-b border-gray-200 shadow-sm sticky top-0 z-40">
    <div class="px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">

            {{-- GRUP KIRI: Tampil di Desktop (Tidak Diubah) --}}
            <div class="hidden lg:flex items-center space-x-8">
                <a href="{{ route('home') }}" class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium {{ request()->routeIs('home') ? 'border-red-500' : 'border-transparent' }} text-gray-500 hover:border-gray-300">Home</a>
                <a href="{{ route('visualisasi.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium {{ request()->routeIs('visualisasi.index') ? 'border-red-500' : 'border-transparent' }} text-gray-500 hover:border-gray-300">Visualisasi Peta</a>
                <a href="{{ route('gallery.publik') }}" class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium {{ request()->routeIs('gallery.publik') ? 'border-red-500' : 'border-transparent' }} text-gray-500 hover:border-gray-300">Galeri</a>
                <a href="{{ route('gallery_maps.peta') }}" class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium {{ request()->routeIs('gallery_maps.peta') ? 'border-red-500' : 'border-transparent' }} text-gray-500 hover:border-gray-300">Galeri Peta</a>
                <a href="{{ route('artikel.publik') }}" class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium {{ request()->routeIs('artikel.publik') ? 'border-red-500' : 'border-transparent' }} text-gray-500 hover:border-gray-300">Artikel</a>
            </div>

            {{-- GRUP KANAN: Tampil di Desktop --}}
            <div class="hidden lg:flex items-center space-x-4">
                {{-- FORM PENCARIAN DIHAPUS DARI SINI --}}

                {{-- Tombol Aksi (Login/Logout/Profile) (Tidak Diubah) --}}
                @auth
                    <div x-data="{ dropdownOpen: false }" class="relative">
                        <button @click="dropdownOpen = !dropdownOpen" class="text-gray-500 hover:text-gray-800 text-xl focus:outline-none"><i class="fas fa-user"></i></button>
                        <div x-show="dropdownOpen" @click.away="dropdownOpen = false" x-transition class="absolute right-0 mt-2 w-48 bg-white border rounded-md shadow-lg z-50">
                            <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Profil Saya</a>
                            @if(auth()->user()->role === 'admin')
                                <a href="{{ route('admin.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Admin Dashboard</a>
                            @elseif(auth()->user()->role === 'editor')
                                <a href="{{ route('editor.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Editor Dashboard</a>
                            @elseif(auth()->user()->role === 'surveyor')
                                <a href="{{ route('surveyor.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Surveyor Dashboard</a>
                            @endif
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Logout</button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="font-semibold text-gray-600 hover:text-gray-900 text-sm">Log in</a>
                    <a href="{{ route('register') }}" class="bg-red-600 text-white text-sm font-bold py-2 px-4 rounded-md hover:bg-red-700">Register</a>
                @endguest
            </div>

            {{-- =============================================== --}}
            {{-- TAMPILAN MOBILE --}}
            {{-- =============================================== --}}
            <div class="flex lg:hidden flex-1 justify-between items-center">
                {{-- Burger Menu --}}
                <button @click="open = !open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24"><path :class="{'hidden': open, 'inline-flex': ! open }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /><path :class="{'hidden': ! open, 'inline-flex': open }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
                
                {{-- Logo Mobile --}}
                <a href="{{ route('home') }}" class="text-xl font-bold text-gray-800"><span class="text-red-600">S</span>ISIRAJA</a>

                {{-- Tombol Search Mobile Dihapus --}}
                {{-- Spacer agar logo tetap di tengah --}}
                <div class="w-8"></div> 
            </div>
        </div>
    </div>


    {{-- Menu Dropdown Mobile (Tidak Diubah) --}}
    <div :class="{'block': open, 'hidden': ! open}" class="hidden lg:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('home')" :active="request()->routeIs('home')">Home</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('visualisasi.index')" :active="request()->routeIs('visualisasi.index')">Visualisasi Peta</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('gallery.publik')" :active="request()->routeIs('gallery.publik')">Galeri</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('artikel.publik')" :active="request()->routeIs('artikel.publik')">Artikel</x-responsive-nav-link>
        </div>

        <div class="pt-4 pb-3 border-t border-gray-200">
            @auth
                <div class="px-4">
                    <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
                </div>
                <div class="mt-3 space-y-1">
                    <x-responsive-nav-link :href="route('profile.edit')">Profil</x-responsive-nav-link>
                     @if(auth()->user()->role === 'admin')
                        <x-responsive-nav-link :href="route('admin.index')">Admin Dashboard</x-responsive-nav-link>
                    @endif
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">Logout</x-responsive-nav-link>
                    </form>
                </div>
            @else
                <x-responsive-nav-link :href="route('login')">Log In</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('register')">Register</x-responsive-nav-link>
            @endauth
        </div>
    </div>
</nav>
