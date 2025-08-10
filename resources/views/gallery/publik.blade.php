@extends('layouts.app')

@push('styles')
{{-- Menambahkan CSS kustom untuk memodifikasi Fancybox (TIDAK DIUBAH) --}}
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
        {{-- Header --}}
        <div class="mb-8 text-center">
            <h1 class="text-4xl font-bold text-gray-800">Galeri</h1>
            <p class="text-lg text-gray-500 mt-2">Jelajahi dokumentasi visual kami.</p>
        </div>

        {{-- PERUBAHAN: Menambahkan Search Bar dan Tab Kategori dalam satu baris --}}
        <div class="flex flex-col md:flex-row items-center justify-between mb-6 border-b border-gray-300 gap-4 pb-2">
            <!-- Tab Kategori -->
            <div class="flex flex-wrap justify-center md:justify-start">
                @php
                    $categories = ['Sesar Aktif', 'Peta Geologi', 'Mitigasi Bencana', 'Studi Lapangan', 'Lainnya'];
                @endphp
                @foreach ($categories as $category)
                    <button data-category="{{ $category }}" 
                            class="gallery-tab text-sm sm:text-base font-medium py-3 px-4 border-b-4 transition-colors duration-300 border-transparent text-gray-500 hover:text-red-600 hover:border-red-300">
                        {{ $category }}
                    </button>
                @endforeach
            </div>

            <!-- Search Input -->
            <div class="relative w-full md:w-auto">
                <input type="text" id="search-input" placeholder="Cari judul atau deskripsi..."
                       class="w-full md:w-64 pl-10 pr-4 py-2 border border-gray-300 rounded-full focus:outline-none focus:ring-2 focus:ring-red-500">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400"></i>
                </div>
            </div>
        </div>


        {{-- Grid untuk menampilkan gambar --}}
        <div id="gallery-grid" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            {{-- Konten dimuat oleh JavaScript --}}
        </div>

        {{-- Container untuk pagination links --}}
        <div id="pagination-links" class="mt-8 flex justify-center"></div>
    </div>
</div>

{{-- Kontainer untuk konten popup Fancybox --}}
<div id="fancybox-popup-content" style="display: none;"></div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const tabs = document.querySelectorAll('.gallery-tab');
    const gridContainer = document.getElementById('gallery-grid');
    const paginationContainer = document.getElementById('pagination-links');
    const fancyboxContainer = document.getElementById('fancybox-popup-content');
    const searchInput = document.getElementById('search-input'); // Ambil elemen search input
    
    const apiBaseUrl = "{{ url('/gallery/category') }}";
    const assetBaseUrl = "{{ asset('gallery/') }}";

    let currentCategory = ''; // Simpan kategori aktif
    let debounceTimer; // Untuk timer debouncing

    // Fungsi untuk membangun URL API dengan parameter
    function buildApiUrl(category, page = 1, searchTerm = '') {
        const encodedCategory = encodeURIComponent(category);
        let url = `${apiBaseUrl}/${encodedCategory}?page=${page}`;
        if (searchTerm) {
            url += `&search=${encodeURIComponent(searchTerm)}`;
        }
        return url;
    }

    async function fetchGallery(url) {
        // Tampilkan loading spinner
        gridContainer.innerHTML = `<div class="h-48 col-span-full flex items-center justify-center text-gray-500"><svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg><span>Memuat galeri...</span></div>`;
        paginationContainer.innerHTML = '';

        try {
            const response = await fetch(url);
            if (!response.ok) throw new Error('Network response was not ok.');
            
            const result = await response.json();
            const images = result.data;
            const links = result.links;

            gridContainer.innerHTML = '';
            fancyboxContainer.innerHTML = '';

            if (images && images.length > 0) {
                images.forEach(image => {
                    const imageUrl = `${assetBaseUrl}/${image.image_path}`;
                    
                    // **PERBAIKAN KRITIS**: Pastikan class "relative" ada di tag <a>
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
                    
                    // Template untuk popup Fancybox (tidak berubah)
                    const popupContentHTML = `
                    <div id="popup-${image.id}" style="display:none; max-width: 100%; width: 100%; height: 100%;">
                        <div class="flex flex-col md:flex-row w-full h-full gap-6">
                            <div class="w-full md:w-2/3 h-full flex items-center justify-center relative overflow-hidden">
                                <img src="${imageUrl}" class="w-auto h-auto max-w-full max-h-full object-contain" draggable="false">
                                <a href="${imageUrl}" download="${image.title.replace(/ /g, '_')}.jpg" class="absolute top-3 right-3 w-10 h-10 bg-black bg-opacity-50 text-white rounded-full hover:bg-opacity-75 transition-all flex items-center justify-center" title="Unduh Gambar">
                                    <i class="fas fa-download"></i>
                                </a>
                            </div>
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

                // Inisialisasi ulang Fancybox
                if (typeof Fancybox !== 'undefined') {
                    Fancybox.destroy();
                    Fancybox.bind('[data-fancybox="gallery"]', {
                        groupAll: false, dragToClose: false, click: false,
                        Panzoom: { mouseWheel: true, panOnlyZoomed: true },
                        Toolbar: false, Thumbs: false,
                        keyboard: { Escape: "close" },
                        template: {
                            closeButton: '<button data-fancybox-close class="fancybox__button" title="Tutup" style="position: absolute; top: 1rem; right: 1rem; z-index: 9999; background: rgba(0,0,0,0.5); color: white; border-radius: 50%; width: 2.5rem; height: 2.5rem; display: flex; align-items: center; justify-content: center;"><i class="fas fa-times"></i></button>',
                        },
                    });
                }
                
                // Render pagination links dari data JSON baru
                if (links && links.length > 2) { // Hanya tampilkan jika ada halaman prev/next
                    paginationContainer.innerHTML = links.map(link => 
                        `<a href="#" class="pagination-link inline-block px-4 py-2 mr-1 mb-1 text-sm font-medium rounded-md ${link.active ? 'bg-red-600 text-white' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50'} ${!link.url ? 'opacity-50 cursor-not-allowed' : ''}" data-url="${link.url}">${link.label.replace('&laquo;', '«').replace('&raquo;', '»')}</a>`
                    ).join('');
                }

            } else {
                gridContainer.innerHTML = `<div class="h-48 col-span-full flex items-center justify-center text-gray-400">Tidak ada gambar yang cocok dengan pencarian Anda.</div>`;
            }
        } catch (error) {
            console.error('Gagal memuat galeri:', error);
            gridContainer.innerHTML = `<div class="h-48 col-span-full flex items-center justify-center text-red-500">Gagal memuat galeri. Silakan coba lagi nanti.</div>`;
        }
    }

    function setActiveTab(category) {
        currentCategory = category; // Update state kategori
        tabs.forEach(t => {
            t.classList.remove('active-tab', 'border-red-600', 'text-red-600');
            t.classList.add('border-transparent', 'text-gray-500');
            if (t.dataset.category === category) {
                t.classList.add('active-tab', 'border-red-600', 'text-red-600');
                t.classList.remove('border-transparent', 'text-gray-500');
            }
        });
        const searchTerm = searchInput.value;
        const url = buildApiUrl(category, 1, searchTerm);
        fetchGallery(url);
    }

    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            setActiveTab(this.dataset.category);
        });
    });

    paginationContainer.addEventListener('click', function(e) {
        const link = e.target.closest('.pagination-link');
        if (link) {
            e.preventDefault();
            const url = link.dataset.url;
            if (url && url !== 'null') {
                fetchGallery(url); // URL dari backend sudah berisi parameter yang benar
            }
        }
    });

    // Event listener untuk input pencarian dengan debouncing
    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            // Saat mencari, panggil setActiveTab untuk me-reset ke halaman 1 dengan term pencarian baru
            setActiveTab(currentCategory);
        }, 500); // Tunggu 500ms setelah user berhenti mengetik
    });

    function initializeGallery() {
        const firstTab = document.querySelector('.gallery-tab');
        if (firstTab) {
            setActiveTab(firstTab.dataset.category);
        }
    }

    initializeGallery();
});
</script>
@endpush
