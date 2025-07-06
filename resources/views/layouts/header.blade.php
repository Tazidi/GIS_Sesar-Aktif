<header class="bg-white shadow-md">
    {{-- Baris paling atas (Ticker) --}}
    <div class="bg-gray-800 text-white text-sm py-1">
        <div class="px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center">
                <div class="flex items-center flex-grow min-w-0">
                    <span class="bg-red-600 font-bold py-1 px-3 flex items-center space-x-2">
                        <i class="fas fa-fire"></i>
                        <span>Trending</span>
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

            <div class="flex items-center space-x-4">
                <button class="text-gray-500 hover:text-gray-800 text-xl"><i class="fas fa-bell"></i></button>
                @guest
                    <a href="{{ route('login') }}" class="text-gray-500 hover:text-gray-800 text-xl"><i class="fas fa-user"></i></a>
                @endguest
                @auth
                    <a href="{{ route('profile.edit') }}" class="text-gray-500 hover:text-gray-800 text-xl"><i class="fas fa-user"></i></a>
                @endauth
                <a href="#" class="bg-red-600 text-white text-sm font-bold py-2 px-4 rounded-md hover:bg-red-700 transition duration-300">
                    Subscribe
                </a>
            </div>
        </div>
    </div>
</header>