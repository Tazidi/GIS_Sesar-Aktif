<header class="bg-white shadow-md">
    {{-- Baris paling atas (Ticker) --}}
    <div class="bg-gray-800 text-white text-sm py-1">
        <div class="px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center">
                <div class="flex items-center flex-grow min-w-0">
                    <span class="bg-red-600 font-bold py-1 px-3 rounded-md mr-4 flex items-center space-x-2 whitespace-nowrap">
                        <i class="fas fa-fire"></i>
                        <span>Trending News</span>
                    </span>
                    <div class="ticker-wrap flex-grow">
                        <div class="ticker-move">
                            <p>berita hot terhot saat hot ini :o.</p>
                        </div>
                    </div>
                </div>
                <div class="hidden md:flex items-center space-x-4 mx-6 shrink-0">
                    <i class="far fa-calendar-alt"></i>
                    <span id="datetime-widget">Memuat waktu...</span>
                </div>
                <div class="hidden md:flex items-center space-x-2 shrink-0">
                    <i class="fas fa-sun text-yellow-400"></i>
                    <span>Bandung, 28°C</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Header Utama yang Baru --}}
    <div class="px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-4">
            <div class="flex items-center space-x-3">
                <a href="#" class="h-8 w-8 rounded-full flex items-center justify-center bg-blue-600 text-white hover:opacity-80 transition-opacity"><i class="fab fa-facebook-f"></i></a>
                <a href="#" class="h-8 w-8 rounded-full flex items-center justify-center bg-gradient-to-br from-purple-600 to-pink-500 text-white hover:opacity-80 transition-opacity"><i class="fab fa-instagram"></i></a>
                <a href="#" class="h-8 w-8 rounded-full flex items-center justify-center bg-gray-900 text-white hover:opacity-80 transition-opacity"><i class="fab fa-x-twitter"></i></a>
                <a href="#" class="h-8 w-8 rounded-full flex items-center justify-center bg-red-600 text-white hover:opacity-80 transition-opacity"><i class="fab fa-youtube"></i></a>
            </div>

            <div class="text-center">
                <h1 class="text-5xl font-extrabold tracking-tight">
                    <span class="text-red-600">S</span><span class="text-gray-900">ISIRAJA</span>
                </h1>
                <p class="mt-1 text-base text-gray-500">
                    Sistem Informasi Sesar Jawa Bagian Barat
                </p>
            </div>

            <div class="flex items-center space-x-4 relative">
                <button class="text-gray-500 hover:text-gray-800 text-xl relative"><i class="fas fa-bell"></i></button>
                @auth
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="text-gray-500 hover:text-gray-800 text-xl focus:outline-none">
                            <i class="fas fa-user"></i>
                        </button>

                        <div
                            x-show="open"
                            @click.away="open = false"
                            x-transition
                            class="absolute right-0 mt-2 w-48 bg-white border rounded-md shadow-lg z-50"
                        >
                            <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Profil</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 text-gray-700 hover:bg-gray-100">
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                @endauth

                @guest
                    <a href="{{ route('login') }}" class="text-gray-500 hover:text-gray-800 text-xl"><i class="fas fa-user"></i></a>
                @endguest

                <a href="#" class="bg-red-600 text-white text-sm font-bold py-2 px-4 rounded-md hover:bg-red-700 transition duration-300">
                    Subscribe
                </a>
            </div>
        </div>
    </div>
</header>