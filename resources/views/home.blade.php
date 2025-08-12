@extends('layouts.app')

@section('content')
<div class="py-8">
    <div class="px-4 sm:px-6 lg:px-8">

        {{-- BAGIAN TOP TAGS & LATEST STORY --}}
        <div class="border-b-2 border-gray-200 pb-4 mb-8">
            <h3 class="font-bold text-gray-500 mb-2"># Top Tags</h3>
            <div class="flex items-center space-x-4">
                <span class="bg-red-600 text-white text-sm font-bold py-1 px-3 flex items-center whitespace-nowrap">
                    <i class="fa-solid fa-clock-rotate-left mr-2"></i>
                    <span>Latest Story</span>
                </span>
                <div class="ticker-wrap flex-grow">
                    <div class="ticker-move">
                        <p class="text-sm text-gray-700">{{ $latestPosts->first()->title ?? 'Belum ada berita terbaru.' }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- STRUKTUR GRID TIGA KOLOM --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-stretch">

            <div class="lg:col-span-3 flex flex-col">
                <div class="mb-4 border-b border-gray-300">
                    <h2 class="text-xl font-bold inline-block pb-2 border-b-4 border-red-600">Latest Post</h2>
                </div>
                
                <div class="flex flex-col space-y-6 flex-grow">
                    @forelse($latestPosts as $post)
                        <a href="{{ route('articles.show', $post) }}" class="flex-1 block group relative overflow-hidden shadow-md rounded-md">
                            <img src="{{ asset('thumbnails/' . basename($post->thumbnail)) }}" alt="{{ $post->title }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent"></div>
                            <div class="absolute bottom-0 p-4 text-white z-10">
                                <span class="bg-red-600 text-white text-xs font-bold py-1 px-2 rounded-md">Latest</span>
                                <h3 class="font-semibold text-lg mt-2">{{ $post->title }}</h3>
                            </div>
                        </a>
                    @empty
                        <div class="bg-white flex-1 rounded-md flex items-center justify-center text-gray-500 shadow-md">
                            <p>Tidak ada post hari ini.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="lg:col-span-6">
                <div class="mb-4 border-b border-gray-300">
                    <h2 class="text-xl font-bold inline-block pb-2 border-b-4 border-red-600">Main Story</h2>
                </div>
                @if($mainStories->isNotEmpty())
                    <div class="grid grid-cols-1 gap-6">
                        <a href="{{ route('articles.show', $mainStories->first()) }}" class="block group relative overflow-hidden shadow-md h-80 rounded-md">
                            <img src="{{ asset('thumbnails/' . basename($mainStories->first()->thumbnail)) }}" alt="{{ $mainStories->first()->title }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent"></div>
                            <div class="absolute bottom-0 p-6 text-white z-10">
                                <h3 class="font-bold text-3xl">{{ $mainStories->first()->title }}</h3>
                            </div>
                        </a>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @foreach($mainStories->skip(1) as $story)
                                <a href="{{ route('articles.show', $story) }}" class="block group relative overflow-hidden shadow-md h-40 rounded-md">
                                     <img src="{{ asset('thumbnails/' . basename($story->thumbnail)) }}" alt="{{ $story->title }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                                    <div class="absolute inset-0 bg-black opacity-0 group-hover:opacity-60 transition-opacity duration-300"></div>
                                    <div class="absolute inset-0 p-4 flex flex-col justify-end transform translate-y-full group-hover:translate-y-0 transition-transform duration-500 ease-in-out">
                                        <h4 class="font-semibold text-white">{{ $story->title }}</h4>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="bg-white h-[500px] flex items-center justify-center text-gray-500 shadow-md rounded-md"><p>Tidak ada cerita utama.</p></div>
                @endif
            </div>

        <div class="lg:col-span-3">
            <div class="mb-4 border-b border-gray-300">
                <h2 class="text-xl font-bold inline-block pb-2 border-b-4 border-red-600">Today Update</h2>
            </div>
            <div class="bg-white p-4 shadow-md rounded-md">
                <div class="flex border-b mb-4">
                    {{-- Tombol 'Popular' telah dihapus. --}}
                    {{-- 'Trending' diubah menjadi 'Most viewed' dan dijadikan tab aktif. --}}
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
                        {{-- Pesan disesuaikan untuk tab "Most viewed" --}}
                        <p class="text-sm text-gray-500">Tidak ada artikel yang paling banyak dilihat.</p>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

        {{-- BAGIAN GALERI --}}
        <div class="mt-12">
            <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-gray-300">
                <div class="flex flex-wrap border-b-0 sm:border-b-0 -mb-px">
                    <button data-category="Sesar Aktif" class="gallery-tab active-tab text-sm font-bold py-2 px-4 border-b-4 border-red-600 text-red-600">Sesar Aktif</button>
                    <button data-category="Peta Geologi" class="gallery-tab text-sm font-bold py-2 px-4 border-b-4 border-transparent text-gray-500 hover:text-red-600">Peta Geologi</button>
                    <button data-category="Mitigasi Bencana" class="gallery-tab text-sm font-bold py-2 px-4 border-b-4 border-transparent text-gray-500 hover:text-red-600">Mitigasi Bencana</button>
                    <button data-category="Studi Lapangan" class="gallery-tab text-sm font-bold py-2 px-4 border-b-4 border-transparent text-gray-500 hover:text-red-600">Studi Lapangan</button>
                    <button data-category="Lainnya" class="gallery-tab text-sm font-bold py-2 px-4 border-b-4 border-transparent text-gray-500 hover:text-red-600">Lainnya</button>
                </div>
                <a href="{{ route('gallery.publik') }}" class="text-sm mt-2 sm:mt-0 font-semibold text-red-600 hover:underline">Lihat Semua Galeri &rarr;</a>
            </div>

            <div id="gallery-grid-home" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                <div class="h-48 col-span-full flex items-center justify-center text-gray-500">Memuat galeri...</div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const tabs = document.querySelectorAll('.gallery-tab');
    const gridContainer = document.getElementById('gallery-grid-home');
    
    // Definisikan URL dasar menggunakan Blade helper
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
                    // PERBAIKAN: Buat link yang benar ke halaman galeri publik dengan parameter kategori
                    const linkUrl = `${galleryPageUrl}?category=${encodeURIComponent(image.category)}`;
                    const imageUrl = `${assetBaseUrl}/${image.image_path}`;

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

    tabs.forEach(tab => {
        tab.addEventListener('click', function () {
            tabs.forEach(t => {
                t.classList.remove('active-tab', 'border-red-600', 'text-red-600');
                t.classList.add('border-transparent', 'text-gray-500');
            });
            this.classList.add('active-tab', 'border-red-600', 'text-red-600');
            this.classList.remove('border-transparent', 'text-gray-500');
            const category = this.dataset.category;
            fetchHomepageGallery(category);
        });
    });

    const initialActiveTab = document.querySelector('.gallery-tab.active-tab');
    if (initialActiveTab) {
        const initialCategory = initialActiveTab.dataset.category;
        fetchHomepageGallery(initialCategory);
    }
});
</script>
@endsection
