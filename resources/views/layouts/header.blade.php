<header class="bg-white shadow-sm">
{{-- Baris paling atas --}}
    <div class="bg-gray-800 text-white py-1">
        <div class="px-4 sm:px-6 lg:px-8">
            {{-- DIUBAH: Menambahkan kelas responsif md:justify-end dan md:gap-6 --}}
            <div class="flex justify-between md:justify-end items-center md:gap-6">
                
                {{-- Waktu & Tanggal --}}
                <div class="flex items-center space-x-2 text-xs sm:text-sm shrink-0">
                    <i class="far fa-calendar-alt"></i>
                    <span id="datetime-widget">Memuat waktu...</span>
                </div>

                {{-- Cuaca --}}
                <div class="flex items-center space-x-2 text-xs sm:text-sm shrink-0">
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
                        <a href="https://www.facebook.com/brin.indonesia/" class="h-8 w-8 rounded-full flex items-center justify-center bg-blue-600 text-white hover:opacity-80"><i class="fab fa-facebook-f"></i></a>
                        <a href="https://www.instagram.com/brin_indonesia/" class="h-8 w-8 rounded-full flex items-center justify-center bg-gradient-to-br from-purple-600 to-pink-500 text-white hover:opacity-80"><i class="fab fa-instagram"></i></a>
                        <a href="https://x.com/brin_indonesia" class="h-8 w-8 rounded-full flex items-center justify-center bg-gray-900 text-white hover:opacity-80"><i class="fab fa-x-twitter"></i></a>
                        <a href="https://www.youtube.com/channel/UCr1ihEI566IJib9P-JjENSA" class="h-8 w-8 rounded-full flex items-center justify-center bg-red-600 text-white hover:opacity-80"><i class="fab fa-youtube"></i></a>
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