<footer class="bg-gray-800 text-gray-400 mt-12">
    <div class="px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            {{-- Kolom Tentang --}}
            <div>
                <h3 class="text-white text-lg font-bold mb-4">Tentang SISIRAJA</h3>
                <p class="text-sm">
                    Sistem Informasi Sesar Jawa Bagian Barat menyediakan data dan visualisasi terkini mengenai aktivitas sesar untuk penelitian dan mitigasi bencana.
                </p>
            </div>

            {{-- Kolom Link Cepat --}}
            <div>
                <h3 class="text-white text-lg font-bold mb-4">Link Cepat</h3>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ route('home') }}" class="hover:text-white">Home</a></li>
                    <li><a href="{{ route('articles.index') }}" class="hover:text-white">Artikel</a></li>
                    <li><a href="{{ route('gallery.index') }}" class="hover:text-white">Galeri</a></li>
                    <li><a href="{{ route('visualisasi.index') }}" class="hover:text-white">Visualisasi Peta</a></li>
                </ul>
            </div>

            {{-- Kolom Media Sosial --}}
            <div>
                <h3 class="text-white text-lg font-bold mb-4">Ikuti Kami</h3>
                <div class="flex items-center space-x-4">
                    <a href="#" class="text-2xl hover:text-blue-500"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="text-2xl hover:text-pink-500"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="text-2xl hover:text-white"><i class="fab fa-x-twitter"></i></a>
                    <a href="#" class="text-2xl hover:text-red-500"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
        </div>

        <div class="mt-8 border-t border-gray-700 pt-6 text-center text-sm">
            <p>&copy; {{ date('Y') }} SISIRAJA. All Rights Reserved.</p>
        </div>
    </div>
</footer>