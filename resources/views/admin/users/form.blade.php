<form action="{{ $user->exists ? route('users.update', $user) : route('users.store') }}" method="POST">
    @csrf
    @if ($user->exists)
        @method('PUT')
    @endif

    <div class="mb-4">
        <label class="block font-medium">Nama</label>
        <input type="text" name="name" class="w-full border rounded px-2 py-1"
               value="{{ old('name', $user->name) }}" required>
    </div>

    <div class="mb-4">
        <label class="block font-medium">Email</label>
        <input type="email" name="email" class="w-full border rounded px-2 py-1"
               value="{{ old('email', $user->email) }}" required>
    </div>

    <div class="mb-4">
        <label class="block font-medium">Role</label>
        <select name="role" class="w-full border rounded px-2 py-1" required>
            <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Admin</option>
            <option value="editor" {{ old('role', $user->role) == 'editor' ? 'selected' : '' }}>Editor</option>
            <option value="user" {{ old('role', $user->role) == 'user' ? 'selected' : '' }}>User</option>
        </select>
    </div>

    @if (!$user->exists)
    <div class="mb-4">
        <label class="block font-medium">Password</label>
        <input type="password" name="password" class="w-full border rounded px-2 py-1" required>
    </div>
    @endif

    <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded">
        {{ $user->exists ? 'Perbarui' : 'Simpan' }}
    </button>
</form>
