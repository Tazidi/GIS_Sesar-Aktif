{{-- 1. State 'searchOpen' ditambahkan untuk mengontrol form pencarian mobile --}}
<nav x-data="{ open: false, searchOpen: false }" class="bg-white border-b border-gray-200 shadow-sm sticky top-0 z-40">
    {{-- 2. Kelas 'max-w-7xl mx-auto' dihapus agar navigasi memenuhi lebar layar --}}
    <div class="px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">

            {{-- GRUP KIRI: Tampil di Desktop --}}
            <div class="hidden lg:flex items-center space-x-8">
                <a href="{{ route('home') }}" class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium {{ request()->routeIs('home') ? 'border-red-500' : 'border-transparent' }} text-gray-500 hover:border-gray-300">Home</a>
                <a href="{{ route('visualisasi.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium {{ request()->routeIs('visualisasi.index') ? 'border-red-500' : 'border-transparent' }} text-gray-500 hover:border-gray-300">Visualisasi Peta</a>
                <a href="{{ route('gallery.publik') }}" class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium {{ request()->routeIs('gallery.publik') ? 'border-red-500' : 'border-transparent' }} text-gray-500 hover:border-gray-300">Galeri</a>
                <a href="{{ route('gallery_maps.peta') }}" class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium {{ request()->routeIs('gallery_maps.peta') ? 'border-red-500' : 'border-transparent' }} text-gray-500 hover:border-gray-300">Galeri Peta</a>

                <a href="{{ route('artikel.publik') }}" class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium {{ request()->routeIs('artikel.publik') ? 'border-red-500' : 'border-transparent' }} text-gray-500 hover:border-gray-300">Artikel</a>            </div>

            {{-- GRUP KANAN: Tampil di Desktop --}}
            <div class="hidden lg:flex items-center space-x-4">
                <form action="#" method="GET" class="relative">
                    <input type="search" name="keyword" placeholder="Cari..." class="w-48 pl-4 pr-10 py-2 border border-gray-300 rounded-full focus:ring-red-500 focus:border-red-500 text-sm">
                    <button type="submit" class="absolute right-0 top-0 mt-2 mr-3 text-gray-400 hover:text-gray-900"><i class="fas fa-search"></i></button>
                </form>

                {{-- Tombol Aksi (Login/Logout/Profile) --}}
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
                {{-- Tampilan header mobile standar (burger, logo, ikon search) --}}
                {{-- Akan disembunyikan saat search aktif --}}
                <div x-show="!searchOpen" class="flex flex-1 justify-between items-center">
                    <button @click="open = !open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24"><path :class="{'hidden': open, 'inline-flex': ! open }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /><path :class="{'hidden': ! open, 'inline-flex': open }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                    
                    <a href="{{ route('home') }}" class="text-xl font-bold text-gray-800"><span class="text-red-600">S</span>ISIRAJA</a>

                    {{-- Tombol search ini sekarang membuka form pencarian --}}
                    <button @click="searchOpen = true" class="text-gray-500 hover:text-gray-800 p-2">
                        <i class="fas fa-search"></i>
                    </button>
                </div>

                {{-- Form pencarian mobile yang akan muncul --}}
                <div x-show="searchOpen" x-transition class="w-full">
                    <form action="#" method="GET" class="relative w-full">
                        <input type="search" name="keyword" placeholder="Ketik untuk mencari..." class="w-full pl-4 pr-10 py-2 border border-gray-300 rounded-full focus:ring-red-500 focus:border-red-500 text-sm">
                        <button @click="searchOpen = false" type="button" class="absolute right-0 top-0 mt-2 mr-3 text-gray-400 hover:text-gray-900">
                            <i class="fas fa-times"></i> {{-- Tombol close --}}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <div :class="{'block': open, 'hidden': ! open}" class="hidden lg:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('home')" :active="request()->routeIs('home')">Home</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('visualisasi.index')" :active="request()->routeIs('visualisasi.index')">Visualisasi Peta</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('gallery.publik')" :active="request()->routeIs('gallery.publik')">Galeri</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('artikel.publik')" :active="request()->routeIs('artikel.publik')">Artikel</x-responsive-nav-link>
        </div>

        {{-- Opsi Pengguna di Menu Responsif --}}
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