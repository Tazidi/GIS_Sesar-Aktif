<header class="bg-white shadow-sm">
    {{-- Baris paling atas (Ticker) --}}
    {{-- DIHAPUS: Kelas 'hidden md:block' dihapus agar baris ini muncul di semua perangkat --}}
    <div class="bg-gray-800 text-white text-sm py-1">
        <div class="px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center">
                {{-- Bagian Trending News (tetap terlihat) --}}
                <div class="flex items-center flex-grow min-w-0">
                    <span class="bg-red-600 font-bold py-1 px-3 rounded-md mr-4 flex items-center space-x-2 whitespace-nowrap">
                        <i class="fas fa-fire"></i>
                        <span>Trending</span>
                    </span>
                    <div class="ticker-wrap flex-grow">
                        <div class="ticker-move">
                            <p>Berita terkini seputar sesar aktif di Jawa Barat.</p>
                        </div>
                    </div>
                </div>

                {{-- DIUBAH: Ditambahkan 'hidden md:flex' untuk menyembunyikan di HP/Tablet --}}
                <div class="hidden md:flex items-center space-x-4 mx-6 shrink-0">
                    <i class="far fa-calendar-alt"></i>
                    <span id="datetime-widget">Memuat waktu...</span>
                </div>

                {{-- DIUBAH: Ditambahkan 'hidden md:flex' untuk menyembunyikan di HP/Tablet --}}
                <div class="hidden md:flex items-center space-x-2 shrink-0">
                    <i class="fas fa-sun text-yellow-400"></i>
                    <span>Bandung, 28°C</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Header Utama (Logo dan Ikon Sosial Media) --}}
    <div class="bg-white border-b">
        <div class="px-4 sm:px-6 lg:px-8">
            <div class="flex justify-center lg:justify-between items-center py-4">
                {{-- Kiri: Ikon Sosial Media (sembunyi di hp/tablet) --}}
                <div class="hidden lg:flex flex-1 justify-start">
                    <div class="flex items-center space-x-3">
                        <a href="#" class="h-8 w-8 rounded-full flex items-center justify-center bg-blue-600 text-white hover:opacity-80"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="h-8 w-8 rounded-full flex items-center justify-center bg-gradient-to-br from-purple-600 to-pink-500 text-white hover:opacity-80"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="h-8 w-8 rounded-full flex items-center justify-center bg-gray-900 text-white hover:opacity-80"><i class="fab fa-x-twitter"></i></a>
                    </div>
                </div>

                {{-- Tengah: Logo --}}
                <div class="text-center">
                    <h1 class="text-4xl lg:text-5xl font-extrabold tracking-tight">
                        <span class="text-red-600">S</span><span class="text-gray-900">ISIRAJA</span>
                    </h1>
                    <p class="mt-1 text-xs sm:text-sm text-gray-500">
                        Sistem Informasi Sesar Jawa Bagian Barat
                    </p>
                </div>

                {{-- Kanan: Spacer (sembunyi di hp/tablet) --}}
                <div class="hidden lg:block flex-1"></div>
            </div>
        </div>
    </div>
</header>