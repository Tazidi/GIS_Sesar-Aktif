<div class="max-w-2xl mx-auto bg-white p-8 shadow-lg rounded-lg">
    
    {{-- Judul Form --}}
    <h1 class="text-2xl font-bold text-gray-800 mb-2">
        {{ $kategori->exists ? 'Edit Kategori' : 'Buat Kategori Baru' }}
    </h1>
    <p class="text-sm text-gray-600 mb-6">
        {{ $kategori->exists ? 'Lakukan perubahan pada detail kategori di bawah ini.' : 'Isi detail untuk kategori baru yang akan Anda buat.' }}
    </p>

    <hr class="mb-6">

    <form action="{{ $kategori->exists ? route('kategori.update', $kategori) : route('kategori.store') }}" method="POST">
        @csrf
        @if ($kategori->exists)
            @method('PUT')
        @endif

        {{-- Nama Kategori --}}
        <div class="mb-5">
            <label for="nama_kategori" class="block text-sm font-medium text-gray-700 mb-1">
                Nama Kategori <span class="text-red-500">*</span>
            </label>
            <input type="text" id="nama_kategori" name="nama_kategori" 
                   value="{{ old('nama_kategori', $kategori->nama_kategori) }}" required
                   class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 
                          focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 
                          @error('nama_kategori') border-red-500 @enderror"
                   placeholder="Contoh: Wisata, Kuliner, Infrastruktur">
            @error('nama_kategori')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Tombol Aksi --}}
        <div class="flex items-center justify-end pt-6 border-t border-gray-200">
            <a href="{{ route('kategori.index') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">
                Batal
            </a>
            <button type="submit" 
                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                {{ $kategori->exists ? 'Update Kategori' : 'Simpan Kategori' }}
            </button>
        </div>
    </form>
</div>
