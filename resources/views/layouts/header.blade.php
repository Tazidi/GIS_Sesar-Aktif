<header class="bg-white shadow-sm">
    {{-- Baris paling atas (Ticker) --}}
    <div class="bg-gray-800 text-white text-sm py-1">
        {{-- Kontainer ini sekarang menggunakan padding, bukan max-width --}}
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

    {{-- Header Utama --}}
    <div class="bg-white border-b">
        {{-- Kontainer ini sekarang menggunakan padding, bukan max-width --}}
        <div class="px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                {{-- Kiri: Ikon Sosial Media --}}
                <div class="flex items-center space-x-3 w-48">
                    <a href="#" class="h-8 w-8 rounded-full flex items-center justify-center bg-blue-600 text-white hover:opacity-80 transition-opacity"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="h-8 w-8 rounded-full flex items-center justify-center bg-gradient-to-br from-purple-600 to-pink-500 text-white hover:opacity-80 transition-opacity"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="h-8 w-8 rounded-full flex items-center justify-center bg-gray-900 text-white hover:opacity-80 transition-opacity"><i class="fab fa-x-twitter"></i></a>
                    <a href="#" class="h-8 w-8 rounded-full flex items-center justify-center bg-red-600 text-white hover:opacity-80 transition-opacity"><i class="fab fa-youtube"></i></a>
                </div>

                {{-- Tengah: Logo SISIRAJA --}}
                <div class="text-center">
                    <h1 class="text-5xl font-extrabold tracking-tight">
                        <span class="text-red-600">S</span><span class="text-gray-900">ISIRAJA</span>
                    </h1>
                    <p class="mt-1 text-base text-gray-500">
                        Sistem Informasi Sesar Jawa Bagian Barat
                    </p>
                </div>

                {{-- Kanan: Diberi lebar yang sama dengan ikon sosial agar logo tetap di tengah --}}
                <div class="w-48"></div>
            </div>
        </div>
    </div>
</header>