@extends('layouts.app')

{{-- Push Styles for DataTables with Tailwind CSS Integration --}}
@push('styles')
    {{-- Use the official DataTables Tailwind CSS theme --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.7/css/dataTables.tailwindcss.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.2/css/responsive.tailwindcss.min.css">
@endpush

@section('content')
<div class="py-12">
    <div class="px-4 mx-auto sm:px-6 lg:px-8 max-w-7xl">
        {{-- Tombol Kembali ke Dashboard --}}
        <div class="mb-5">
            <a href="{{ route('dashboard') }}"
               class="inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-700 transition bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M7.707 14.707a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l2.293 2.293a1 1 0 010 1.414z" clip-rule="evenodd"></path></svg>
                Kembali ke Dashboard
            </a>
        </div>

        {{-- Judul Halaman dan Tombol Create --}}
        <div class="flex items-center justify-between pb-2 mb-8 border-b border-gray-300">
            <h2 class="inline-block pb-2 text-3xl font-bold border-b-4 border-red-600">Semua Artikel</h2>

            @auth
                @if (auth()->user()->role === 'editor' || auth()->user()->role === 'admin')
                    <a href="{{ route('articles.create') }}"
                       class="inline-flex items-center px-4 py-2 font-semibold text-blue-700 transition duration-300 bg-white border border-blue-700 rounded-lg shadow-sm hover:bg-blue-700 hover:text-white">
                        <svg class="w-5 h-5 mr-2 -ml-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
                        </svg>
                        Buat Artikel
                    </a>
                @endif
            @endauth
        </div>

        {{-- Tabel Artikel --}}
        <div class="p-4 bg-white rounded-lg shadow-md">
            <table id="articlesTable" class="w-full text-sm" style="width:100%">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 font-medium text-left text-gray-500 uppercase">No</th>
                        <th class="px-4 py-3 font-medium text-left text-gray-500 uppercase">Thumbnail</th>
                        <th class="px-4 py-3 font-medium text-left text-gray-500 uppercase">Judul</th>
                        <th class="px-4 py-3 font-medium text-left text-gray-500 uppercase">Author</th>
                        <th class="px-4 py-3 font-medium text-left text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 font-medium text-left text-gray-500 uppercase">Dilihat</th>
                        <th class="px-4 py-3 font-medium text-left text-gray-500 uppercase">Tanggal Terbit</th>
                        <th class="px-4 py-3 font-medium text-left text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach ($articles as $article)
                        <tr class="transition-colors duration-150 hover:bg-gray-50">
                            <td class="px-4 py-4 text-gray-500 whitespace-nowrap">{{ $loop->iteration }}</td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                @if($article->thumbnail)
                                    <img src="{{ asset('thumbnails/' . basename($article->thumbnail)) }}"
                                         alt="Thumbnail"
                                         class="object-cover w-16 h-10 rounded">
                                @else
                                    <div class="flex items-center justify-center w-16 h-10 text-xs text-gray-500 bg-gray-200 rounded">No Image</div>
                                @endif
                            </td>
                            <td class="px-4 py-4 font-medium text-gray-900 whitespace-nowrap">
                                <a href="{{ route('articles.show', $article) }}" class="hover:text-red-600">
                                    {{ Str::limit($article->title, 40) }}
                                </a>
                            </td>
                            <td class="px-4 py-4 text-gray-500 whitespace-nowrap">{{ $article->author ?? 'N/A' }}</td>
                            <td class="px-4 py-4 whitespace-nowrap">
                                @php
                                    $statusClasses = [
                                        'approved' => 'bg-green-100 text-green-800',
                                        'pending' => 'bg-yellow-100 text-yellow-800',
                                        'rejected' => 'bg-red-100 text-red-800',
                                        'revision' => 'bg-blue-100 text-blue-800',
                                        'default' => 'bg-gray-100 text-gray-800'
                                    ];
                                    $statusClass = $statusClasses[$article->status] ?? $statusClasses['default'];
                                @endphp
                                <span class="inline-flex px-2 text-xs font-semibold leading-5 rounded-full {{ $statusClass }}">
                                    {{ ucfirst($article->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-4 text-gray-500 whitespace-nowrap">{{ $article->visit_count ?? 0 }}</td>
                            <td class="px-4 py-4 text-gray-500 whitespace-nowrap">{{ $article->created_at->format('d M Y') }}</td>
                            <td class="px-4 py-4 text-sm font-medium whitespace-nowrap">
                                @auth
                                    @php
                                        $user = auth()->user();
                                        $isOwner = $user->id === $article->user_id;
                                        $isEditor = $user->role === 'editor';
                                        $isAdmin = $user->role === 'admin';
                                    @endphp

                                    <div class="flex items-center space-x-4">
                                        @if ($isAdmin || ($isEditor && $isOwner))
                                            <a href="{{ route('articles.edit', $article) }}" class="flex items-center text-blue-600 hover:text-blue-900" title="Edit">
                                                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z" /><path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd" /></svg>
                                            </a>
                                            <form action="{{ route('articles.destroy', $article) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus artikel ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="flex items-center text-red-600 hover:text-red-900" title="Delete">
                                                   <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 006 3.75v.443c-.795.077-1.58.22-2.365.468a.75.75 0 10.23 1.482l.149-.022.841 10.518A2.75 2.75 0 007.596 19h4.807a2.75 2.75 0 002.742-2.53l.841-10.52.149.023a.75.75 0 00.23-1.482A41.03 41.03 0 0014 4.193V3.75A2.75 2.75 0 0011.25 1h-2.5zM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4zM8.58 7.72a.75.75 0 00-1.5.06l.3 7.5a.75.75 0 101.5-.06l-.3-7.5zm4.34.06a.75.75 0 10-1.5-.06l-.3 7.5a.75.75 0 101.5.06l.3-7.5z" clip-rule="evenodd" /></svg>
                                                </button>
                                            </form>
                                        @endif

                                        @if ($isAdmin)
                                            <form method="POST" action="{{ route('articles.updateStatus', $article) }}">
                                                @csrf
                                                @method('PATCH')
                                                <select name="status"
                                                        onchange="this.form.submit()"
                                                        class="py-1 pl-2 text-xs text-gray-700 bg-white border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-red-500">
                                                    @foreach (['pending', 'approved', 'rejected', 'revision'] as $status)
                                                        <option value="{{ $status }}" {{ $article->status === $status ? 'selected' : '' }}>
                                                            {{ ucfirst($status) }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </form>
                                        @endif
                                    </div>
                                @endauth
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

{{-- Push Scripts for DataTables with Tailwind Integration --}}
@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    {{-- Make sure to load the Tailwind-specific JS for DataTables --}}
    <script src="https://cdn.datatables.net/2.0.7/js/dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/2.0.7/js/dataTables.tailwindcss.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/3.0.2/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/3.0.2/js/responsive.tailwindcss.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#articlesTable').DataTable({
                responsive: true,
                language: {
                    url: '//cdn.datatables.net/plug-ins/2.0.7/i18n/id.json',
                },
            });
        });
    </script>
@endpush