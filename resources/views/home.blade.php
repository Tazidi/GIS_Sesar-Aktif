@extends('layouts.app')

@section('content')
<div class="py-8">
    <div class="px-4 sm:px-6 lg:px-8">

        {{-- BAGIAN HEADER ARTIKEL BARU --}}
        <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-gray-300">
            <h2 class="text-xl font-bold inline-block pb-2 border-b-4 border-red-600 text-red-600">
                Artikel
            </h2>
            <a href="{{ route('artikel.publik') }}" class="inline-flex items-center gap-2 bg-red-600 text-white text-sm font-semibold px-4 py-2 rounded-md hover:bg-red-700 transition-colors mt-2 sm:mt-0">
                <i class="fas fa-newspaper"></i>
                <span>Lihat Semua Artikel</span>
            </a>
        </div>

        {{-- STRUKTUR GRID TIGA KOLOM --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-stretch">
            
            {{-- Kolom Kiri: Latest Post --}}
            <div class="lg:col-span-3 flex flex-col">
                <div class="mb-4 border-b border-gray-300">
                    <h2 class="text-xl font-bold inline-block pb-2 border-b-4 border-red-600">Latest Post</h2>
                </div>
                {{-- PERUBAHAN 2: Mengubah layout mobile menjadi 2 kolom --}}
                <div class="grid grid-cols-2 gap-4 lg:flex lg:flex-col lg:space-y-6 lg:gap-0">
                    @forelse($latestPosts as $post)
                        {{-- Menyesuaikan tinggi agar konsisten di mobile --}}
                        <a href="{{ route('articles.show', $post) }}" class="h-48 lg:h-auto lg:flex-1 block group relative overflow-hidden shadow-md rounded-md">
                            <img src="{{ asset('thumbnails/' . basename($post->thumbnail)) }}" alt="{{ $post->title }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent"></div>
                            <div class="absolute bottom-0 p-2 lg:p-4 text-white z-10">
                                <span class="bg-red-600 text-white text-xs font-bold py-1 px-2 rounded-md">Latest</span>
                                <h3 class="font-semibold text-sm lg:text-lg mt-2">{{ \Illuminate\Support\Str::limit($post->title, 40) }}</h3>
                            </div>
                        </a>
                    @empty
                        <div class="col-span-2 bg-white flex-1 rounded-md flex items-center justify-center text-gray-500 shadow-md">
                            <p>Tidak ada post hari ini.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Kolom Tengah: Paling Banyak Dilihat (Slider) --}}
            <div class="lg:col-span-6 flex flex-col">
                <div class="mb-4 border-b border-gray-300">
                    <h2 class="text-xl font-bold inline-block pb-2 border-b-4 border-red-600">Paling Banyak Dilihat</h2>
                </div>

                @if($mainStories->isNotEmpty())
                    <div id="slider-container" class="relative h-96 lg:h-auto lg:flex-grow rounded-md shadow-lg overflow-hidden">
                        <div id="slider-wrapper" class="h-full w-full">
                            @foreach($mainStories as $article)
                                <a href="{{ route('articles.show', $article) }}" class="slider-item absolute top-0 left-0 w-full h-full block group transition-opacity duration-700 ease-in-out">
                                    <img src="{{ asset('thumbnails/' . basename($article->thumbnail)) }}" alt="{{ $article->title }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 to-transparent"></div>
                                    <div class="absolute inset-0 p-6 flex flex-col justify-end text-white transform translate-y-full group-hover:translate-y-0 transition-all duration-500 ease-in-out bg-gradient-to-t from-black/90 via-black/50 to-transparent">
                                        <h3 class="font-bold text-2xl drop-shadow-lg">{{ $article->title }}</h3>
                                        <p class="mt-2 text-sm text-gray-200 drop-shadow-lg">
                                            {{ \Illuminate\Support\Str::limit(strip_tags($article->content), 120) }}
                                        </p>
                                    </div>
                                    <div class="absolute bottom-0 left-0 p-6 text-white z-10 group-hover:opacity-0 transition-opacity duration-300">
                                        <h3 class="font-bold text-3xl drop-shadow-lg">{{ $article->title }}</h3>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                        <button id="slider-prev" class="absolute top-1/2 -translate-y-1/2 left-4 z-20 w-12 h-12 bg-black/40 text-white rounded-full hover:bg-red-600 transition-colors flex items-center justify-center">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <button id="slider-next" class="absolute top-1/2 -translate-y-1/2 right-4 z-20 w-12 h-12 bg-black/40 text-white rounded-full hover:bg-red-600 transition-colors flex items-center justify-center">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                @else
                    <div class="bg-white h-96 lg:h-auto lg:flex-grow flex items-center justify-center text-gray-500 shadow-md rounded-md"><p>Tidak ada artikel untuk ditampilkan.</p></div>
                @endif
            </div>

            {{-- Kolom Kanan: Today Update --}}
            <div class="lg:col-span-3">
                <div class="mb-4 border-b border-gray-300">
                    <h2 class="text-xl font-bold inline-block pb-2 border-b-4 border-red-600">Today Update</h2>
                </div>
                <div class="bg-white p-4 shadow-md rounded-md">
                    <div class="flex border-b mb-4">
                        <button class="flex-1 py-2 text-sm font-bold bg-red-600 text-white rounded-t-md"><i class="fas fa-fire mr-2"></i>Most viewed</button>
                        <button class="flex-1 py-2 text-sm text-gray-500 hover:text-gray-800"><i class="far fa-clock mr-2"></i>Recent</button>
                    </div>
                    <ul>
                        @forelse($popularArticles as $article)
                            <li class="border-b py-3 last:border-b-0">
                                <a href="{{ route('articles.show', $article) }}" class="font-semibold text-gray-800 hover:text-red-600">{{ $article->title }}</a>
                                <p class="text-xs text-gray-500 mt-1">{{ $article->created_at->format('d F Y') }}</p>
                            </li>
                        @empty
                            <p class="text-sm text-gray-500">Tidak ada artikel populer saat ini.</p>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

        {{-- BAGIAN PETA --}}
        <div class="mt-14">
            {{-- PERUBAHAN 4: Menyesuaikan layout tombol Peta --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-gray-300">
                <h2 class="text-xl font-bold inline-block pb-2 border-b-4 border-red-600 text-red-600">Peta SISIRAJA</h2>
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 mt-2 sm:mt-0">
                    <a href="{{ route('gallery_maps.peta') }}" class="inline-flex items-center justify-center gap-2 bg-gray-200 text-gray-800 text-sm font-semibold px-4 py-2 rounded-md hover:bg-gray-300 transition-colors">
                        <i class="fas fa-images"></i>
                        <span>Lihat Galeri Peta</span>
                    </a>
                    <a href="{{ route('visualisasi.index') }}" class="inline-flex items-center justify-center gap-2 bg-red-600 text-white text-sm font-semibold px-4 py-2 rounded-md hover:bg-red-700 transition-colors">
                        <i class="fas fa-map-marked-alt"></i>
                        <span>Lihat Peta SISIRAJA</span>
                    </a>
                </div>
            </div>
            <div class="p-2 shadow-md rounded-md" style="height: 450px;">
                <iframe src="{{ route('visualisasi.index', ['embed' => true]) }}" style="width: 100%; height: 100%; border: none; border-radius: 8px;" loading="lazy"></iframe>
            </div>
        </div>
        
        {{-- BAGIAN GALERI --}}
        <div class="mt-12">
            {{-- PERUBAHAN 1: Mengubah layout Galeri --}}
            <div class="mb-6 flex flex-col lg:flex-row lg:items-center lg:justify-between border-b border-gray-300">
                {{-- Kategori Navigasi --}}
                <div class="relative lg:flex-grow flex items-center">
                    {{-- Tombol Navigasi Mobile --}}
                    <button id="gallery-mobile-prev" class="lg:hidden absolute -left-1 z-10 p-1 bg-white/80 rounded-full shadow-md text-gray-600 hover:bg-gray-200"><i class="fas fa-chevron-left"></i></button>
                    
                    {{-- Wrapper untuk Kategori --}}
                    <div id="gallery-cat-container" class="flex w-full justify-center lg:justify-start whitespace-nowrap overflow-x-hidden pb-2 -mb-px">
                        <button data-category="Sesar Aktif" class="gallery-tab active-tab text-sm font-bold py-2 px-4 border-b-4 border-red-600 text-red-600 flex-shrink-0">Sesar Aktif</button>
                        <button data-category="Peta Geologi" class="gallery-tab text-sm font-bold py-2 px-4 border-b-4 border-transparent text-gray-500 hover:text-red-600 flex-shrink-0">Peta Geologi</button>
                        <button data-category="Mitigasi Bencana" class="gallery-tab text-sm font-bold py-2 px-4 border-b-4 border-transparent text-gray-500 hover:text-red-600 flex-shrink-0">Mitigasi Bencana</button>
                        <button data-category="Studi Lapangan" class="gallery-tab text-sm font-bold py-2 px-4 border-b-4 border-transparent text-gray-500 hover:text-red-600 flex-shrink-0">Studi Lapangan</button>
                        <button data-category="Lainnya" class="gallery-tab text-sm font-bold py-2 px-4 border-b-4 border-transparent text-gray-500 hover:text-red-600 flex-shrink-0">Lainnya</button>
                    </div>
                    
                    {{-- Tombol Navigasi Mobile --}}
                    <button id="gallery-mobile-next" class="lg:hidden absolute -right-1 z-10 p-1 bg-white/80 rounded-full shadow-md text-gray-600 hover:bg-gray-200"><i class="fas fa-chevron-right"></i></button>
                </div>
                {{-- Tombol Lihat Semua (Hanya di Desktop) --}}
                <a href="{{ route('gallery.publik') }}" class="hidden lg:inline-flex items-center gap-2 bg-red-600 text-white text-sm font-semibold px-4 py-2 rounded-md hover:bg-red-700 transition-colors ml-4">
                    <i class="fas fa-images"></i>
                    <span>Lihat Semua Galeri</span>
                </a>
            </div>
            
            <div id="gallery-grid-home" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                <div class="h-48 col-span-full flex items-center justify-center text-gray-500">Memuat galeri...</div>
            </div>

            {{-- PERUBAHAN 3: Tombol Lihat Semua (Hanya di Mobile) --}}
            <div class="flex justify-center mt-6 lg:hidden">
                 <a href="{{ route('gallery.publik') }}" class="inline-flex items-center gap-2 bg-red-600 text-white text-xs font-semibold px-3 py-1.5 rounded-md hover:bg-red-700 transition-colors">
                    <i class="fas fa-images"></i>
                    <span>Lihat Semua Galeri</span>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')

{{-- Pustaka Leaflet (selalu dimuat) --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
window.addEventListener('load', function () {
    
    // Script untuk Galeri (Fetch Data)
    const galleryTabs = document.querySelectorAll('.gallery-tab');
    const gridContainer = document.getElementById('gallery-grid-home');
    if (galleryTabs.length > 0 && gridContainer) {
        const galleryPageUrl = "{{ route('gallery.publik') }}";
        const assetBaseUrl = "{{ asset('gallery/') }}";
        const apiBaseUrl = "{{ url('/gallery/category') }}";
        async function fetchHomepageGallery(category) {
            gridContainer.innerHTML = `<div class="h-48 col-span-full flex items-center justify-center text-gray-500">Memuat galeri...</div>`;
            try {
                const encodedCategory = encodeURIComponent(category);
                const response = await fetch(`${apiBaseUrl}/${encodedCategory}/home`);
                if (!response.ok) throw new Error('Network response was not ok.');
                const images = await response.json();
                gridContainer.innerHTML = '';
                if (images.length > 0) {
                    images.forEach(image => {
                        const linkUrl = `${galleryPageUrl}?category=${encodeURIComponent(image.category)}`;
                        const imageUrl = `${assetBaseUrl}/${image.main_image}`;
                        const galleryItem = `
                            <a href="${linkUrl}" class="relative block aspect-square group overflow-hidden rounded-md shadow-md">
                                <img src="${imageUrl}" alt="${image.title}" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-110">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent flex flex-col justify-end p-3">
                                    <h3 class="font-bold text-white text-sm">${image.title}</h3>
                                </div>
                            </a>
                        `;
                        gridContainer.insertAdjacentHTML('beforeend', galleryItem);
                    });
                } else {
                    gridContainer.innerHTML = `<div class="h-48 col-span-full flex items-center justify-center text-gray-400">Tidak ada gambar dalam kategori ini.</div>`;
                }
            } catch (error) {
                console.error('Gagal memuat galeri:', error);
                gridContainer.innerHTML = `<div class="h-48 col-span-full flex items-center justify-center text-red-500">Gagal memuat galeri.</div>`;
            }
        }
        
        // PERUBAHAN 1: Logika baru untuk navigasi kategori galeri
        let currentCatIndex = 0;

        function updateActiveCategory(index) {
            galleryTabs.forEach((tab, i) => {
                // Update tampilan untuk mobile (hanya satu yang terlihat)
                tab.classList.toggle('hidden', i !== index && window.innerWidth < 1024);
                tab.classList.toggle('lg:flex', window.innerWidth >= 1024);

                // Update style active
                if (i === index) {
                    tab.classList.add('active-tab', 'border-red-600', 'text-red-600');
                    tab.classList.remove('border-transparent', 'text-gray-500');
                } else {
                    tab.classList.remove('active-tab', 'border-red-600', 'text-red-600');
                    tab.classList.add('border-transparent', 'text-gray-500');
                }
            });
            fetchHomepageGallery(galleryTabs[index].dataset.category);
        }

        galleryTabs.forEach((tab, index) => {
            tab.addEventListener('click', function () {
                currentCatIndex = index;
                updateActiveCategory(currentCatIndex);
            });
        });

        const prevCatBtn = document.getElementById('gallery-mobile-prev');
        const nextCatBtn = document.getElementById('gallery-mobile-next');

        if(prevCatBtn && nextCatBtn) {
            prevCatBtn.addEventListener('click', () => {
                currentCatIndex = (currentCatIndex - 1 + galleryTabs.length) % galleryTabs.length;
                updateActiveCategory(currentCatIndex);
            });

            nextCatBtn.addEventListener('click', () => {
                currentCatIndex = (currentCatIndex + 1) % galleryTabs.length;
                updateActiveCategory(currentCatIndex);
            });
        }
        
        // Panggil saat load dan resize untuk menyesuaikan tampilan
        updateActiveCategory(currentCatIndex);
        window.addEventListener('resize', () => updateActiveCategory(currentCatIndex));
    }
    
    // Script untuk Slider Artikel
    const sliderContainer = document.getElementById('slider-container');
    if (sliderContainer) {
        const slides = sliderContainer.querySelectorAll('.slider-item');
        const prevButton = document.getElementById('slider-prev');
        const nextButton = document.getElementById('slider-next');
        
        if (slides.length > 0) {
            let currentIndex = 0;
            let autoPlayInterval;

            function showSlide(index) {
                slides.forEach((slide, i) => {
                    slide.style.opacity = (i === index) ? '1' : '0';
                    slide.style.zIndex = (i === index) ? '10' : '1';
                });
            }

            function nextSlide() {
                currentIndex = (currentIndex + 1) % slides.length;
                showSlide(currentIndex);
            }

            function prevSlide() {
                currentIndex = (currentIndex - 1 + slides.length) % slides.length;
                showSlide(currentIndex);
            }

            function startAutoPlay() {
                autoPlayInterval = setInterval(nextSlide, 5000);
            }

            function stopAutoPlay() {
                clearInterval(autoPlayInterval);
            }

            nextButton.addEventListener('click', () => {
                stopAutoPlay();
                nextSlide();
                startAutoPlay();
            });

            prevButton.addEventListener('click', () => {
                stopAutoPlay();
                prevSlide();
                startAutoPlay();
            });

            showSlide(currentIndex);
            startAutoPlay();
        }
    }

});
</script>

@endpush
    