@extends('layouts.app')

@section('content')
<div class="py-12 bg-gray-50" x-data="{ filterOpen: false }">
    <div class="px-4 sm:px-6 lg:px-8">

        {{-- PERUBAHAN: Form dihilangkan, elemen diberi ID untuk JavaScript --}}
        <div class="mb-8 pb-4 border-b border-gray-200">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <div>
                    <h2 class="text-3xl font-bold text-gray-800">Artikel Publik</h2>
                    <p class="text-sm text-gray-500 mt-1">Jelajahi semua artikel yang telah dipublikasikan.</p>
                </div>
                
                <div class="flex items-center gap-2 w-full md:w-auto">
                    <div class="relative flex-grow">
                        {{-- ID 'search-input' ditambahkan --}}
                        <input type="text" id="search-input" name="search" placeholder="Cari artikel..." value="{{ request('search') }}"
                               class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                    </div>

                    <div class="relative">
                        <button type="button" @click="filterOpen = !filterOpen" class="flex items-center gap-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg px-4 py-2 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L13 10.414V15a1 1 0 01-.293.707l-2 2A1 1 0 019 17v-6.586L4.293 6.707A1 1 0 014 6V3z" clip-rule="evenodd" /></svg>
                            <span>Filter & Urutkan</span>
                        </button>

                        <div x-show="filterOpen" @click.away="filterOpen = false" x-transition class="absolute z-20 top-full right-0 mt-2 w-screen max-w-xs sm:max-w-sm bg-white shadow-xl rounded-lg p-6" style="display: none;">
                            <h3 class="text-xl font-semibold mb-4 pb-3 border-b border-gray-200 text-gray-800">Filter & Urutkan</h3>
                            <div class="space-y-6">
                                <div>
                                    <label for="tag-filter" class="block text-sm font-medium text-gray-700 mb-1">Filter berdasarkan Tag</label>
                                    {{-- ID 'tag-filter' ditambahkan --}}
                                    <select name="tag" id="tag-filter" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">Semua Tag</option>
                                        @foreach ($tags as $tag)
                                            <option value="{{ $tag }}" {{ request('tag') == $tag ? 'selected' : '' }}>{{ $tag }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label for="sort-filter" class="block text-sm font-medium text-gray-700 mb-1">Urutkan berdasarkan</label>
                                    {{-- ID 'sort-filter' ditambahkan --}}
                                    <select name="sort" id="sort-filter" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="created_at" {{ request('sort', 'created_at') == 'created_at' ? 'selected' : '' }}>Tanggal Terbit</option>
                                        <option value="title" {{ request('sort') == 'title' ? 'selected' : '' }}>Judul Artikel</option>
                                        <option value="author" {{ request('sort') == 'author' ? 'selected' : '' }}>Nama Penulis</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="order-filter" class="block text-sm font-medium text-gray-700 mb-1">Urutan</label>
                                    {{-- ID 'order-filter' ditambahkan --}}
                                    <select name="order" id="order-filter" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="desc" {{ request('order', 'desc') == 'desc' ? 'selected' : '' }}>Terbaru / Z-A</option>
                                        <option value="asc" {{ request('order') == 'asc' ? 'selected' : '' }}>Terlama / A-Z</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- PERUBAHAN: Kontainer diberi ID agar bisa dimanipulasi JavaScript --}}
        <div id="article-list-container">
            @include('partials.article_list', ['articles' => $articles])
        </div>

        <div id="pagination-container" class="mt-10">
            {{ $articles->links() }}
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.x.x/dist/alpine.min.js" defer></script>
@endsection

{{-- PENAMBAHAN: Seluruh blok script ini baru --}}
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('search-input');
    const tagFilter = document.getElementById('tag-filter');
    const sortFilter = document.getElementById('sort-filter');
    const orderFilter = document.getElementById('order-filter');
    const articleContainer = document.getElementById('article-list-container');
    const paginationContainer = document.getElementById('pagination-container');

    let debounceTimer;

    async function fetchArticles(page = 1) {
        const search = searchInput.value;
        const tag = tagFilter.value;
        const sort = sortFilter.value;
        const order = orderFilter.value;

        // Tampilkan loading indicator dengan ikon
        articleContainer.innerHTML = `<div class="flex items-center justify-center text-gray-500 p-12"><svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg><span>Memuat artikel...</span></div>`;
        paginationContainer.innerHTML = '';

        // Bangun URL dengan parameter
        const url = `{{ route('artikel.publik') }}?page=${page}&search=${search}&tag=${tag}&sort=${sort}&order=${order}`;

        try {
            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest', // Header penting agar controller tahu ini AJAX
                    'Accept': 'application/json',
                }
            });
            const data = await response.json();

            // Ganti konten dengan hasil render dari server
            articleContainer.innerHTML = data.contentHTML;
            paginationContainer.innerHTML = data.paginationHTML;
        } catch (error) {
            console.error('Gagal memuat artikel:', error);
            articleContainer.innerHTML = `<div class="text-center p-12 text-red-500">Gagal memuat artikel.</div>`;
        }
    }

    // Fungsi untuk memicu pencarian dengan debouncing
    function triggerFetch() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            fetchArticles(1); // Selalu kembali ke halaman 1 saat filter berubah
        }, 500); // Tunggu 500ms setelah user berhenti mengetik/memilih
    }

    // Tambahkan event listener ke semua elemen filter
    searchInput.addEventListener('input', triggerFetch);
    tagFilter.addEventListener('change', triggerFetch);
    sortFilter.addEventListener('change', triggerFetch);
    orderFilter.addEventListener('change', triggerFetch);

    // Tangani klik pada link paginasi
    document.body.addEventListener('click', function(e) {
        if (e.target.matches('.pagination a')) {
            e.preventDefault();
            const url = new URL(e.target.href);
            const page = url.searchParams.get('page');
            fetchArticles(page);
        }
    });
});
</script>
@endpush
