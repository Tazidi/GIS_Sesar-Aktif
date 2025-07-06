@extends('layouts.app')

@section('content')
<div class="py-8">
    {{-- Kontainer utama --}}
    <div class="px-4 sm:px-6 lg:px-8">

        {{-- BAGIAN TOP TAGS & LATEST STORY --}}
        <div class="border-b-2 border-gray-200 pb-4 mb-8">
            <h3 class="font-bold text-gray-500 mb-2"># Top Tags</h3>
            <div class="flex items-center space-x-4">
                <span class="bg-red-600 text-white text-sm font-bold py-1 px-3 flex items-center">
                    <i class="fa-solid fa-clock-rotate-left mr-2"></i>
                    <span>Latest Story</span>
                </span>
                <div class="ticker-wrap flex-grow">
                    <div class="ticker-move">
                        <p class="text-sm text-gray-700">testing 123 nopal ganteng.</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- STRUKTUR GRID TIGA KOLOM --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            <div class="lg:col-span-3">
                <div class="mb-4 border-b border-gray-300">
                    <h2 class="text-xl font-bold inline-block pb-2 border-b-4 border-red-600">Today Post</h2>
                </div>
                <div class="space-y-6">
                    <div class="bg-gray-800 text-white p-6 shadow-md h-[236px] flex flex-col justify-end">
                        <div>
                            <span class="bg-red-600 text-white text-xs font-bold py-1 px-2">Uncategorized</span>
                            <h3 class="font-bold text-2xl mt-4">Hello world!</h3>
                            <div class="text-xs text-gray-400 mt-4 flex items-center space-x-4">
                                <span><i class="far fa-user mr-1"></i> By asep_surasep</span>
                                <span><i class="far fa-calendar-alt mr-1"></i> November 22, 2024</span>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-800 text-white p-6 shadow-md h-[236px] flex flex-col justify-end">
                         <div>
                            <span class="bg-red-600 text-white text-xs font-bold py-1 px-2">Tech</span>
                            <h3 class="font-bold text-2xl mt-4">Contoh Post Kedua</h3>
                            <div class="text-xs text-gray-400 mt-4 flex items-center space-x-4">
                                <span><i class="far fa-user mr-1"></i> By asep_surasep</span>
                                <span><i class="far fa-calendar-alt mr-1"></i> November 23, 2024</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="lg:col-span-6">
                <div class="mb-4 border-b border-gray-300">
                    <h2 class="text-xl font-bold inline-block pb-2 border-b-4 border-red-600">Main Story</h2>
                </div>
                <div class="bg-gray-300 h-[500px] flex items-center justify-center shadow-md">
                    <p class="text-gray-500">(Konten Main Story akan ada di sini)</p>
                </div>
            </div>
            <div class="lg:col-span-3">
                <div class="mb-4 border-b border-gray-300">
                    <h2 class="text-xl font-bold inline-block pb-2 border-b-4 border-red-600">Today Update</h2>
                </div>
                <div class="bg-white p-4 shadow-md">
                    <div class="flex border-b mb-4">
                        <button class="flex-1 py-2 text-sm font-bold bg-red-600 text-white"><i class="fas fa-fire mr-2"></i>Popular</button>
                        <button class="flex-1 py-2 text-sm text-gray-500 hover:text-gray-800"><i class="fas fa-chart-line mr-2"></i>Trending</button>
                        <button class="flex-1 py-2 text-sm text-gray-500 hover:text-gray-800"><i class="far fa-clock mr-2"></i>Recent</button>
                    </div>
                    <ul>
                       <li class="border-b py-3">
                            <a href="#" class="font-semibold text-gray-800 hover:text-red-600">Hello world!</a>
                            <p class="text-xs text-gray-500 mt-1">November 22, 2024</p>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- BAGIAN GALERI --}}
        <div class="mt-12">
            <div class="mb-4 border-b border-gray-300">
                <h2 class="text-xl font-bold inline-block pb-2 border-b-4 border-red-600">Galeri</h2>
            </div>
            {{-- Strukturnya harus seperti ini: .swiper > .swiper-wrapper > .swiper-slide --}}
            <div class="swiper h-80 w-full relative">
                <div class="swiper-wrapper">
                    @for ($i = 1; $i <= 10; $i++)
                        <div class="swiper-slide group relative overflow-hidden">
                            <img src="https://picsum.photos/seed/gallery{{$i}}/1200/800" class="w-full h-full object-cover transition-transform duration-500 ease-in-out group-hover:scale-110" alt="Gambar Galeri {{$i}}">
                            <div class="absolute inset-0 bg-black opacity-0 group-hover:opacity-50 transition-opacity duration-300 pointer-events-none"></div>
                            <div class="absolute inset-0 p-6 flex flex-col justify-end transform translate-y-full group-hover:translate-y-0 transition-transform duration-500 ease-in-out pointer-events-none">
                                <h3 class="text-white font-bold text-2xl">Judul Gambar {{$i}}</h3>
                                <p class="text-gray-200 text-sm mt-2">gambarnya bagus yah wow</p>
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
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const swiper = new Swiper('.swiper', {
            loop: true,
            autoplay: {
                delay: 5000,
                disableOnInteraction: false,
            },
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
        });
    });
</script>
@endsection