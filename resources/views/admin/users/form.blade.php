<form action="{{ $user->exists ? route('users.update', $user) : route('users.store') }}" method="POST" class="bg-white shadow-md rounded px-6 py-6 space-y-6">
    @csrf
    @if ($user->exists)
        @method('PUT')
    @endif

    <div>
        <label class="block font-semibold text-gray-700 mb-1">Nama</label>
        <input type="text" name="name" required
               class="w-full border-gray-300 rounded px-3 py-2 shadow-sm focus:ring focus:ring-red-200"
               value="{{ old('name', $user->name) }}">
    </div>

    <div>
        <label class="block font-semibold text-gray-700 mb-1">Email</label>
        <input type="email" name="email" required
               class="w-full border-gray-300 rounded px-3 py-2 shadow-sm focus:ring focus:ring-red-200"
               value="{{ old('email', $user->email) }}">
    </div>

    <div>
        <label class="block font-semibold text-gray-700 mb-1">Role</label>
        <select name="role" required
                class="w-full border-gray-300 rounded px-3 py-2 shadow-sm focus:ring focus:ring-red-200">
            <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Admin</option>
            <option value="editor" {{ old('role', $user->role) == 'editor' ? 'selected' : '' }}>Editor</option>
            <option value="user" {{ old('role', $user->role) == 'user' ? 'selected' : '' }}>User</option>
        </select>
    </div>

    @if (!$user->exists)
        <div>
            <label class="block font-semibold text-gray-700 mb-1">Password</label>
            <input type="password" name="password" required
                   class="w-full border-gray-300 rounded px-3 py-2 shadow-sm focus:ring focus:ring-red-200">
        </div>
    @endif

    <div class="pt-4">
        <button type="submit"
                class="bg-green-600 text-white font-semibold px-5 py-2 rounded hover:bg-green-700 transition">
            {{ $user->exists ? 'Perbarui' : 'Simpan' }}
        </button>
    </div>
</form>
