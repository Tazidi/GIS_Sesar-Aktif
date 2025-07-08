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
                        <p class="text-sm text-gray-700">{{ $todayPosts->first()->title ?? 'Belum ada berita terbaru hari ini.' }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- STRUKTUR GRID TIGA KOLOM --}}
        {{-- Penambahan 'items-stretch' untuk membantu menyamakan tinggi kolom --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-stretch">

            <div class="lg:col-span-3 flex flex-col">
                <div class="mb-4 border-b border-gray-300">
                    <h2 class="text-xl font-bold inline-block pb-2 border-b-4 border-red-600">Today Post</h2>
                </div>
                
                {{-- REVISI 1: Menggunakan flex-grow agar kartu mengisi ruang vertikal --}}
                <div class="flex flex-col space-y-6 flex-grow">
                    @forelse($todayPosts as $post)
                        <a href="{{ route('articles.show', $post) }}" class="flex-1 block group relative overflow-hidden shadow-md rounded-md">
                            <img src="{{ asset('storage/' . $post->thumbnail) }}" alt="{{ $post->title }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
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
                            <img src="{{ asset('storage/' . $mainStories->first()->thumbnail) }}" alt="{{ $mainStories->first()->title }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent"></div>
                            <div class="absolute bottom-0 p-6 text-white z-10">
                                <h3 class="font-bold text-3xl">{{ $mainStories->first()->title }}</h3>
                            </div>
                        </a>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @foreach($mainStories->skip(1) as $story)
                                <a href="{{ route('articles.show', $story) }}" class="block group relative overflow-hidden shadow-md h-40 rounded-md">
                                     <img src="{{ asset('storage/' . $story->thumbnail) }}" alt="{{ $story->title }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
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
                        <button class="flex-1 py-2 text-sm font-bold bg-red-600 text-white rounded-t-md"><i class="fas fa-fire mr-2"></i>Popular</button>
                        <button class="flex-1 py-2 text-sm text-gray-500 hover:text-gray-800"><i class="fas fa-chart-line mr-2"></i>Trending</button>
                        <button class="flex-1 py-2 text-sm text-gray-500 hover:text-gray-800"><i class="far fa-clock mr-2"></i>Recent</button>
                    </div>
                    <ul>
                        @forelse($popularArticles as $article)
                            <li class="border-b py-3 last:border-b-0">
                                <a href="{{ route('articles.show', $article) }}" class="font-semibold text-gray-800 hover:text-red-600">{{ $article->title }}</a>
                                <p class="text-xs text-gray-500 mt-1">{{ $article->created_at->format('d F Y') }}</p>
                            </li>
                        @empty
                            <p class="text-sm text-gray-500">Tidak ada artikel populer.</p>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

        {{-- BAGIAN GALERI --}}
        <div class="mt-12">
            <div class="mb-4 border-b border-gray-300">
                <h2 class="text-xl font-bold inline-block pb-2 border-b-4 border-red-600">Galeri</h2>
            </div>
            <div class="min-w-0">
                <div class="swiper h-80 w-full relative rounded-md overflow-hidden">
                    <div class="swiper-wrapper">
                        @for ($i = 1; $i <= 10; $i++)
                            <div class="swiper-slide group">
                                <img src="https://picsum.photos/seed/gallery{{$i}}/1200/800" class="w-full h-full object-cover" alt="Gambar Galeri {{$i}}">
                                <div class="absolute inset-0 bg-black opacity-0 group-hover:opacity-50 transition-opacity duration-300 pointer-events-none"></div>
                                <div class="absolute inset-0 p-6 flex flex-col justify-end transform translate-y-full group-hover:translate-y-0 transition-transform duration-500 ease-in-out pointer-events-none">
                                    <h3 class="text-white font-bold text-2xl">Judul Gambar {{$i}}</h3>
                                    <p class="text-gray-200 text-sm mt-2">Ini adalah deskripsi singkat untuk gambar di galeri.</p>
                                </div>
                            </div>
                        @endfor
                    </div>
                    <div class="swiper-button-prev text-white"></div>
                    <div class="swiper-button-next text-white"></div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const swiper = new Swiper('.swiper', {
            loop: true,
            autoplay: { delay: 5000, disableOnInteraction: false },
            navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' },
        });
    });
</script>
@endsection