<form action="{{ $user->exists ? route('users.update', $user) : route('users.store') }}" method="POST" class="bg-white shadow-md rounded-lg p-8 space-y-6">
    @csrf
    @if ($user->exists)
        @method('PUT')
    @endif

    {{-- Nama --}}
    <div>
        <label for="name" class="block font-semibold text-gray-700 mb-1">Nama</label>
        <input type="text" id="name" name="name" required
               class="w-full border-gray-300 rounded-md px-3 py-2 shadow-sm focus:ring focus:ring-red-200"
               value="{{ old('name', $user->name) }}">
    </div>

    {{-- Email --}}
    <div>
        <label for="email" class="block font-semibold text-gray-700 mb-1">Email</label>
        <input type="email" id="email" name="email" required
               class="w-full border-gray-300 rounded-md px-3 py-2 shadow-sm focus:ring focus:ring-red-200"
               value="{{ old('email', $user->email) }}">
    </div>

    {{-- Role --}}
    <div>
        <label for="role" class="block font-semibold text-gray-700 mb-1">Role</label>
        <select id="role" name="role" required
                class="w-full border-gray-300 rounded-md px-3 py-2 shadow-sm focus:ring focus:ring-red-200">
            <option value="admin" @selected(old('role', $user->role) == 'admin')>Admin</option>
            <option value="editor" @selected(old('role', $user->role) == 'editor')>Editor</option>
            <option value="user" @selected(old('role', 'user') == 'user')>User</option>
        </select>
    </div>

    {{-- Password (hanya untuk user baru) --}}
    @if (!$user->exists)
        <div>
            <label for="password" class="block font-semibold text-gray-700 mb-1">Password</label>
            <input type="password" id="password" name="password" required
                   class="w-full border-gray-300 rounded-md px-3 py-2 shadow-sm focus:ring focus:ring-red-200">
        </div>
    @endif
    
    <div class="flex items-center justify-end space-x-4 pt-4 border-t border-gray-200">
        {{-- Tombol Batal (sebagai link untuk kembali) --}}
        <a href="{{ route('users.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">
            Batal
        </a>

        {{-- Tombol Simpan/Perbarui --}}
        <button type="submit"
                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
            {{ $user->exists ? 'Perbarui' : 'Simpan' }}
        </button>
    </div>
</form>