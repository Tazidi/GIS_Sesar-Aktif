@extends('layouts.app')

@push('styles')
{{-- Menambahkan CSS kustom untuk memodifikasi Fancybox dan Carousel --}}
<style>
    /* 1. Menghilangkan SEMUA tombol bawaan Fancybox (TIDAK DIUBAH) */
    .fancybox__button--arrow,
    .fancybox__button--zoom,
    .fancybox__button--slideshow,
    .fancybox__button--fullscreen,
    .fancybox__button--thumbs,
    .fancybox__button--close {
        display: none !important;
    }
    .fancybox__toolbar { display: none !important; }

    /* 2. Kustomisasi layout popup utama (TIDAK DIUBAH) */
    .fancybox__slide.is-selected { background: rgba(0, 0, 0, 0.85); }
    .fancybox__slide.is-selected .fancybox__content {
        background: transparent;
        padding: 2rem;
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    /* 3. Mengubah kursor saat gambar bisa di-pan (TIDAK DIUBAH) */
    .fancybox__content > .f-panzoom.is-panning,
    .fancybox__content > .f-panzoom.is-draggable .f-panzoom__content {
        cursor: grabbing !important;
    }
    .fancybox__content > .f-panzoom.is-draggable .f-panzoom__content {
        cursor: grab !important;
    }

    /* 4. CSS untuk Thumbnail Carousel (TIDAK DIUBAH) */
    .carousel-thumb {
        border: 3px solid transparent;
        transition: border-color 0.3s ease;
        opacity: 0.6;
    }
    .carousel-thumb:hover,
    .carousel-thumb.active {
        border-color: #ef4444; /* Warna merah */
        opacity: 1;
    }
    /* Kustomisasi scrollbar untuk carousel */
    .carousel-container::-webkit-scrollbar {
        width: 4px;
    }
    .carousel-container::-webkit-scrollbar-track {
        background: #444;
    }
    .carousel-container::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }
    .carousel-container::-webkit-scrollbar-thumb:hover {
        background: #ef4444;
    }

/* 5. PERUBAHAN: CSS untuk tombol navigasi popup */
.popup-nav-button {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(0, 0, 0, 0.4);
    color: white;
    border: none;
    border-radius: 50%;
    width: 4rem;      /* Diubah dari 3.5rem (64px) */
    height: 4rem;     /* Diubah dari 3.5rem (64px) */
    font-size: 1.75rem; /* Diubah dari 1.25rem (Ukuran ikon panah) */
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background-color 0.2s ease;
    z-index: 20; /* Pastikan di atas gambar */
}
    .popup-nav-button:hover {
        background: rgba(0, 0, 0, 0.7);
    }
    /* PERUBAHAN: Posisi tombol disesuaikan untuk parent container */
    .popup-nav-button.prev {
        left: 1rem; 
    }
    .popup-nav-button.next {
        right: 1rem;
    }
    /* Sembunyikan di layar kecil agar tidak menutupi gambar */
    @media (max-width: 768px) {
        .popup-nav-button {
            display: none;
        }
    }

/* 6. CSS BARU: Untuk animasi slide pada gambar utama */
.main-image-wrapper {
    /* Transisi untuk properti transform dan opacity selama 0.3 detik */
    transition: transform 0.3s ease-in-out, opacity 0.3s ease-in-out;
    transform: translateX(0);
    opacity: 1;
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Kelas untuk animasi keluar ke kiri (saat klik 'next') */
.main-image-wrapper.slide-out-to-left {
    transform: translateX(-50px); /* Geser 50px ke kiri */
    opacity: 0; /* Pudar */
}

/* Kelas untuk animasi keluar ke kanan (saat klik 'prev') */
.main-image-wrapper.slide-out-to-right {
    transform: translateX(50px); /* Geser 50px ke kanan */
    opacity: 0; /* Pudar */
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

        {{-- Search Bar dan Tab Kategori (TIDAK DIUBAH) --}}
        <div class="flex flex-col md:flex-row items-center justify-between mb-6 border-b border-gray-300 gap-4 pb-2">
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
{{-- PERUBAHAN: Menambahkan fungsi global untuk interaksi carousel --}}
<script>
    function switchPopupImage(event, newSrc, mainImageId, downloadLinkId) {
        // Ganti gambar utama
        document.getElementById(mainImageId).src = newSrc;
        // Ganti link download
        document.getElementById(downloadLinkId).href = newSrc;

        // Atur status 'active' pada thumbnail
        const currentThumb = event.currentTarget;
        const container = currentThumb.closest('.carousel-container');
        if (container) {
            container.querySelectorAll('.carousel-thumb').forEach(thumb => thumb.classList.remove('active'));
            currentThumb.classList.add('active');
        }
    }

    // FUNGSI BARU untuk navigasi gambar internal dengan tombol panah
// GANTI SELURUH FUNGSI INI
function navigateInternal(carouselId, direction) {
    const carouselContainer = document.getElementById(carouselId);
    if (!carouselContainer) return;

    // Ambil wrapper gambar utama yang akan dianimasikan
    // Kita ambil ID gambar dari ID carousel untuk menemukannya
    const imageId = carouselId.replace('carousel-container-', '');
    const mainImageWrapper = document.getElementById(`main-image-wrapper-${imageId}`);
    if (!mainImageWrapper) return;

    const thumbs = Array.from(carouselContainer.querySelectorAll('.carousel-thumb'));
    const activeThumb = carouselContainer.querySelector('.carousel-thumb.active');
    if (!activeThumb || thumbs.length <= 1) return;

    const currentIndex = thumbs.indexOf(activeThumb);
    let nextIndex;

    // Tentukan kelas animasi berdasarkan arah navigasi
    if (direction === 'next') {
        nextIndex = (currentIndex + 1) % thumbs.length;
        mainImageWrapper.classList.add('slide-out-to-left'); // Animasi keluar ke kiri
    } else { // 'prev'
        nextIndex = (currentIndex - 1 + thumbs.length) % thumbs.length;
        mainImageWrapper.classList.add('slide-out-to-right'); // Animasi keluar ke kanan
    }
    
    // Tunggu animasi keluar berjalan sejenak, lalu ganti gambar dan mulai animasi masuk
    setTimeout(() => {
        // Picu klik pada thumbnail berikutnya untuk mengganti gambar (memakai logika lama)
        if (thumbs[nextIndex]) {
            thumbs[nextIndex].click();
        }

        // Hapus kelas animasi. Karena ada 'transition' di CSS,
        // wrapper akan otomatis kembali ke posisi semula (transform: translateX(0), opacity: 1)
        // menciptakan efek animasi "masuk".
        mainImageWrapper.classList.remove('slide-out-to-left', 'slide-out-to-right');
    }, 150); // Delay 150ms (setengah dari durasi transisi 0.3s)
}
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const tabs = document.querySelectorAll('.gallery-tab');
    const gridContainer = document.getElementById('gallery-grid');
    const paginationContainer = document.getElementById('pagination-links');
    const fancyboxContainer = document.getElementById('fancybox-popup-content');
    const searchInput = document.getElementById('search-input');
    
    const apiBaseUrl = "{{ url('/gallery/category') }}";
    const assetBaseUrl = "{{ asset('gallery/') }}";

    let currentCategory = '';
    let debounceTimer;

    function buildApiUrl(category, page = 1, searchTerm = '') {
        const encodedCategory = encodeURIComponent(category);
        let url = `${apiBaseUrl}/${encodedCategory}?page=${page}`;
        if (searchTerm) {
            url += `&search=${encodeURIComponent(searchTerm)}`;
        }
        return url;
    }

    async function fetchGallery(url) {
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
                    const mainImageUrl = `${assetBaseUrl}/${image.main_image}`;

                    const extraImages = Array.isArray(image.extra_images) ? image.extra_images : (image.extra_images ? JSON.parse(image.extra_images) : []);
                    const totalImages = 1 + extraImages.length;
                    
                    const galleryItemHTML = `
                    <a href="#popup-${image.id}" data-fancybox="gallery" class="group relative block w-full overflow-hidden rounded-lg shadow-lg">
                        <div class="aspect-video bg-gray-200">
                            <img src="${mainImageUrl}" alt="${image.title}" class="w-full h-full object-cover transition-transform duration-500 ease-in-out group-hover:scale-110">
                        </div>
                        ${totalImages > 1 ? `
                        <div class="absolute top-2 right-2 bg-black/70 text-white text-xs px-2 py-1 rounded">
                            <i class="fas fa-images mr-1"></i> ${totalImages} foto
                        </div>
                        ` : ''}
                        <div class="absolute bottom-0 left-0 right-0 p-4 bg-gradient-to-t from-black/80 to-transparent text-white transform translate-y-full group-hover:translate-y-0 transition-transform duration-300 ease-in-out">
                            <h3 class="font-bold text-base truncate">${image.title}</h3>
                            <p class="text-sm opacity-90 mt-1 truncate">${image.description || 'Klik untuk melihat detail'}</p>
                        </div>
                    </a>
                    `;
                    gridContainer.insertAdjacentHTML('beforeend', galleryItemHTML);
                    
                    const allImageFiles = [image.main_image, ...extraImages];
                    const mainPopupImageId = `main-popup-image-${image.id}`;
                    const downloadLinkId = `download-link-${image.id}`;
                    const carouselId = `carousel-container-${image.id}`;

                    const thumbnailsHTML = allImageFiles.map((imgFile, index) => {
                        const fullUrl = `${assetBaseUrl}/${imgFile}`;
                        return `<img src="${fullUrl}" class="carousel-thumb w-full h-16 object-cover mb-2 rounded cursor-pointer ${index === 0 ? 'active' : ''}" onclick="switchPopupImage(event, '${fullUrl}', '${mainPopupImageId}', '${downloadLinkId}')">`;
                    }).join('');

                    // **PERUBAHAN STRUKTUR HTML UNTUK POSISI TOMBOL**
                    // **PERUBAHAN STRUKTUR HTML UNTUK POSISI TOMBOL**
                    const popupContentHTML = `
                    <div id="popup-${image.id}" style="display:none; max-width: 100%; width: 100%; height: 100%;">
                        <div class="flex flex-col md:flex-row w-full h-full gap-6">
                            
                            <div class="w-full md:w-2/3 h-full flex items-center justify-center gap-2">
                                
                                ${totalImages > 1 ? `
                                <div id="${carouselId}" class="flex-shrink-0 w-24 h-full overflow-y-auto pr-2 carousel-container">
                                    ${thumbnailsHTML}
                                </div>
                                ` : ''}

                                <div class="relative flex-grow h-full w-full flex items-center justify-center">

                                    ${totalImages > 1 ? `
                                    <button onclick="navigateInternal('${carouselId}', 'prev')" class="popup-nav-button prev" title="Sebelumnya">
                                        <i class="fas fa-chevron-left"></i>
                                    </button>
                                    ` : ''}

                                    <div class="flex-grow h-full w-full flex items-center justify-center overflow-hidden">
                                        <div id="main-image-wrapper-${image.id}" class="main-image-wrapper">
                                            <img src="${mainImageUrl}" id="${mainPopupImageId}" class="w-auto h-auto max-w-full max-h-full object-contain" draggable="false">
                                        </div>
                                        
                                        <a href="${mainImageUrl}" id="${downloadLinkId}" download="${image.title.replace(/ /g, '_')}.jpg" class="absolute top-3 right-3 w-10 h-10 bg-black bg-opacity-50 text-white rounded-full hover:bg-opacity-75 transition-all flex items-center justify-center" title="Unduh Gambar">
                                            <i class="fas fa-download"></i>
                                        </a>
                                    </div>
                                    
                                    ${totalImages > 1 ? `
                                    <button onclick="navigateInternal('${carouselId}', 'next')" class="popup-nav-button next" title="Berikutnya">
                                        <i class="fas fa-chevron-right"></i>
                                    </button>
                                    ` : ''}

                                </div> </div>

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
                        groupAll: true, 
                        dragToClose: false, click: false,
                        Panzoom: { mouseWheel: true, panOnlyZoomed: true },
                        Toolbar: false, Thumbs: false,
                        keyboard: { 
                            Escape: "close",
                            ArrowLeft: "prev",
                            ArrowRight: "next",
                        },
                        template: {
                            closeButton: '<button data-fancybox-close class="fancybox__button" title="Tutup" style="position: absolute; top: 1rem; right: 1rem; z-index: 9999; background: rgba(0,0,0,0.5); color: white; border-radius: 50%; width: 2.5rem; height: 2.5rem; display: flex; align-items: center; justify-content: center;"><i class="fas fa-times"></i></button>',
                        },
                        on: {
                            ready: (fancybox) => {
                                const container = fancybox.container;
                                if (!container) return;

                                let touchstartX = 0;
                                let touchendX = 0;
                                let touchstartY = 0;
                                let touchendY = 0;

                                container.addEventListener('touchstart', e => {
                                    if (e.target.closest('a, button, .carousel-thumb, .carousel-container, .prose')) {
                                        return;
                                    }
                                    touchstartX = e.changedTouches[0].screenX;
                                    touchstartY = e.changedTouches[0].screenY;
                                }, { passive: true });

                                container.addEventListener('touchend', e => {
                                     if (e.target.closest('a, button, .carousel-thumb, .carousel-container, .prose')) {
                                        return;
                                    }
                                    touchendX = e.changedTouches[0].screenX;
                                    touchendY = e.changedTouches[0].screenY;
                                    handleGesture();
                                }, { passive: true });

                                function handleGesture() {
                                    const deltaX = touchendX - touchstartX;
                                    const deltaY = touchendY - touchstartY;

                                    if (Math.abs(deltaY) > Math.abs(deltaX)) {
                                        return;
                                    }
                                    
                                    if (Math.abs(deltaX) > 50) {
                                        if (touchendX < touchstartX) {
                                            fancybox.next();
                                        }
                                        if (touchendX > touchstartX) {
                                            fancybox.prev();
                                        }
                                    }
                                }
                            }
                        }
                    });
                }
                
                // Render pagination (TIDAK DIUBAH)
                if (links && links.length > 2) {
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

    // Sisa dari script (event listener untuk tab, pagination, search) tidak diubah
    function setActiveTab(category) {
        currentCategory = category;
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
                fetchGallery(url);
            }
        }
    });

    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            setActiveTab(currentCategory);
        }, 500);
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
