@extends('layouts.app')

@push('styles')
{{-- Menambahkan CSS kustom untuk memodifikasi Fancybox --}}
<style>
    /* 1. Menghilangkan SEMUA tombol bawaan Fancybox */
    .fancybox__button--arrow,
    .fancybox__button--zoom,
    .fancybox__button--slideshow,
    .fancybox__button--fullscreen,
    .fancybox__button--thumbs,
    .fancybox__button--close {
        display: none !important;
    }

    /* Menghilangkan toolbar bawah (thumbnail) */
    .fancybox__toolbar {
        display: none !important;
    }

    /* 2. Kustomisasi layout popup utama agar transparan dan full-screen */
    .fancybox__slide.is-selected {
        background: rgba(0, 0, 0, 0.85); /* Latar belakang gelap transparan */
    }

    .fancybox__slide.is-selected .fancybox__content {
        background: transparent;
        padding: 2rem; /* Beri jarak dari tepi layar */
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    /* 3. Mengubah kursor saat gambar bisa di-pan (digeser saat zoom) */
    .fancybox__content > .f-panzoom.is-panning,
    .fancybox__content > .f-panzoom.is-draggable .f-panzoom__content {
        cursor: grabbing !important;
    }
    .fancybox__content > .f-panzoom.is-draggable .f-panzoom__content {
        cursor: grab !important;
    }
</style>
@endpush

@section('content')
<div class="py-12">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Header dengan Tab Kategori --}}
        <div class="mb-8 text-center">
            <h1 class="text-4xl font-bold text-gray-800">Galeri</h1>
            <p class="text-lg text-gray-500 mt-2">Jelajahi dokumentasi visual kami.</p>
        </div>
        <div class="mb-6 flex flex-wrap justify-center border-b border-gray-300">
            @php
                $categories = ['Sesar Aktif', 'Peta Geologi', 'Mitigasi Bencana', 'Studi Lapangan', 'Lainnya'];
            @endphp
            @foreach ($categories as $category)
                <button data-category="{{ $category }}" 
                        class="gallery-tab text-sm sm:text-base font-bold py-3 px-5 border-b-4 transition-colors duration-300 border-transparent text-gray-500 hover:text-red-600 hover:border-red-300">
                    {{ $category }}
                </button>
            @endforeach
        </div>

        {{-- Grid untuk menampilkan gambar --}}
        <div id="gallery-grid" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <div class="h-48 col-span-full flex items-center justify-center text-gray-500">
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                <span>Memuat galeri...</span>
            </div>
        </div>

        {{-- Container untuk pagination links --}}
        <div id="pagination-links" class="mt-8 flex justify-center"></div>
    </div>
</div>

<div id="fancybox-popup-content" style="display: none;"></div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const tabs = document.querySelectorAll('.gallery-tab');
    const gridContainer = document.getElementById('gallery-grid');
    const paginationContainer = document.getElementById('pagination-links');
    const fancyboxContainer = document.getElementById('fancybox-popup-content');
    
    const apiBaseUrl = "{{ url('/gallery/category') }}";
    const assetBaseUrl = "{{ asset('gallery/') }}";

    async function fetchGallery(url) {
        gridContainer.innerHTML = `<div class="h-48 col-span-full flex items-center justify-center text-gray-500"><svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg><span>Memuat galeri...</span></div>`;
        paginationContainer.innerHTML = '';
        try {
            const response = await fetch(url);
            if (!response.ok) throw new Error('Network response was not ok.');
            const result = await response.json();
            const images = result.data;
            gridContainer.innerHTML = '';
            fancyboxContainer.innerHTML = '';
            if (images && images.length > 0) {
                images.forEach(image => {
                    const imageUrl = `${assetBaseUrl}/${image.image_path}`;
                    
                    const galleryItemHTML = `
                    <a href="#popup-${image.id}" data-fancybox="gallery" class="group relative block w-full overflow-hidden rounded-lg shadow-lg">
                        <div class="aspect-video bg-gray-200">
                            <img src="${imageUrl}" alt="${image.title}" class="w-full h-full object-cover transition-transform duration-500 ease-in-out group-hover:scale-110">
                        </div>
                        <div class="absolute bottom-0 left-0 right-0 p-4 bg-gradient-to-t from-black/80 to-transparent text-white transform translate-y-full group-hover:translate-y-0 transition-transform duration-300 ease-in-out">
                            <h3 class="font-bold text-base truncate">${image.title}</h3>
                            <p class="text-sm opacity-90 mt-1 truncate">${image.description || 'Klik untuk melihat detail'}</p>
                        </div>
                    </a>
                    `;
                    gridContainer.insertAdjacentHTML('beforeend', galleryItemHTML);
                    
                    const popupContentHTML = `
                    <div id="popup-${image.id}" style="display:none; max-width: 100%; width: 100%; height: 100%;">
                        <div class="flex flex-col md:flex-row w-full h-full gap-6">
                            <!-- Kolom Kiri (Gambar Statis) -->
                            <div class="w-full md:w-2/3 h-full flex items-center justify-center relative overflow-hidden image-viewer-container">
                                <img src="${imageUrl}" class="w-auto h-auto max-w-full max-h-full object-contain" draggable="false" ondragstart="return false;">
                                
                                <div class="absolute top-3 right-3 flex space-x-2">
                                    <a href="${imageUrl}" download="${image.title.replace(/ /g, '_')}.jpg" class="w-10 h-10 bg-black bg-opacity-50 text-white rounded-full hover:bg-opacity-75 transition-all flex items-center justify-center" title="Unduh Gambar">
                                        <i class="fas fa-download"></i>
                                    </a>
                                </div>
                            </div>

                            <!-- Kolom Kanan (Deskripsi Scrollable) -->
                            <div class="w-full md:w-1/3 h-full flex flex-col bg-white rounded-lg shadow-xl overflow-hidden">
                                <div class="p-6 flex-grow overflow-y-auto">
                                    <h3 class="text-2xl font-bold text-gray-800 mb-2">${image.title}</h3>
                                    <p class="text-sm text-white bg-red-600 inline-block px-2 py-1 rounded mb-4">${image.category}</p>
                                    <div class="prose max-w-none text-gray-600">
                                        ${image.description || 'Tidak ada deskripsi.'}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>`;
                    fancyboxContainer.insertAdjacentHTML('beforeend', popupContentHTML);
                });

                if (typeof Fancybox !== 'undefined') {
                    Fancybox.destroy();
                    Fancybox.bind('[data-fancybox="gallery"]', {
                        // --- PERBAIKAN UTAMA DI SINI ---
                        // 1. Matikan pengelompokan gambar untuk mematikan semua navigasi
                        groupAll: false,
                        groupAttr: false,

                        // 2. Matikan interaksi klik dan seret (drag) yang tidak perlu
                        dragToClose: false,
                        click: false,
                        
                        // 3. Aktifkan dan konfigurasikan Panzoom untuk zoom dengan mouse
                        Panzoom: {
                            // Izinkan zoom dengan scroll mouse
                            mouseWheel: true,
                            // Hanya izinkan menggeser (pan) gambar saat sudah di-zoom
                            panOnlyZoomed: true,
                        },

                        // 4. Matikan semua tombol bawaan
                        Toolbar: false,
                        Thumbs: false,
                        
                        // 5. Atur keyboard agar hanya ESC yang berfungsi untuk menutup
                        keyboard: {
                            Escape: "close",
                            Delete: false, Backspace: false, PageUp: false, PageDown: false,
                            ArrowUp: false, ArrowDown: false, ArrowLeft: false, ArrowRight: false,
                        },

                        // 6. Gunakan template tombol close kustom
                        template: {
                            closeButton: '<button data-fancybox-close class="fancybox__button" title="Tutup" style="position: absolute; top: 1rem; right: 1rem; z-index: 9999; background: rgba(0,0,0,0.5); color: white; border-radius: 50%; width: 2.5rem; height: 2.5rem; display: flex; align-items: center; justify-content: center;"><i class="fas fa-times"></i></button>',
                        },
                    });
                }
                if (result.links) {
                    paginationContainer.innerHTML = result.links.map(link => `<a href="${link.url || '#'}" class="pagination-link inline-block px-4 py-2 mr-1 mb-1 text-sm font-medium rounded-md ${link.active ? 'bg-red-600 text-white' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50'} ${!link.url ? 'opacity-50 cursor-not-allowed' : ''}" data-url="${link.url}">${link.label.replace('&laquo;', '«').replace('&raquo;', '»')}</a>`).join('');
                }
            } else {
                gridContainer.innerHTML = `<div class="h-48 col-span-full flex items-center justify-center text-gray-400">Tidak ada gambar dalam kategori ini.</div>`;
            }
        } catch (error) {
            console.error('Gagal memuat galeri:', error);
            gridContainer.innerHTML = `<div class="h-48 col-span-full flex items-center justify-center text-red-500">Gagal memuat galeri. Silakan coba lagi nanti.</div>`;
        }
    }

    function setActiveTab(category) {
        tabs.forEach(t => {
            t.classList.remove('active-tab', 'border-red-600', 'text-red-600');
            if (t.dataset.category === category) {
                t.classList.add('active-tab', 'border-red-600', 'text-red-600');
            }
        });
        const encodedCategory = encodeURIComponent(category);
        fetchGallery(`${apiBaseUrl}/${encodedCategory}`);
    }

    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const category = this.dataset.category;
            setActiveTab(category);
        });
    });

    paginationContainer.addEventListener('click', function(e) {
        const link = e.target.closest('.pagination-link');
        if (link) {
            e.preventDefault();
            const url = link.dataset.url;
            if (url && url !== 'null') {
                fetchGallery(url);
            }
        }
    });

    function initializeGallery() {
        const urlParams = new URLSearchParams(window.location.search);
        const categoryFromUrl = urlParams.get('category');

        if (categoryFromUrl) {
            setActiveTab(decodeURIComponent(categoryFromUrl));
        } else {
            const firstTab = document.querySelector('.gallery-tab');
            if (firstTab) {
                setActiveTab(firstTab.dataset.category);
            }
        }
    }

    initializeGallery();
});
</script>
@endsection
