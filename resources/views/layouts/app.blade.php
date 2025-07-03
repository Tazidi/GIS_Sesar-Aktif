<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{-- 1. Script Alpine.js ditambahkan di sini untuk interaktivitas --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @yield('styles')
</head>

<body class="font-sans antialiased">
    {{-- 2. Seluruh body dibungkus dengan div yang memiliki state Alpine.js --}}
    <div x-data="{ sidebarOpen: false }" class="relative">

        {{-- 3. Sidebar Merah ditambahkan di sini --}}
        <aside
            class="fixed top-0 left-0 h-screen w-64 bg-red-800 text-white transform transition-transform duration-300 ease-in-out z-50"
            :class="{'translate-x-0': sidebarOpen, '-translate-x-full': !sidebarOpen}"
            x-cloak
        >
            <div class="p-4 text-2xl font-bold border-b border-red-700">
                Dashboard
            </div>
            <nav class="mt-4">
                <a href="{{ route('home') }}" class="block py-2.5 px-4 rounded transition duration-200 hover:bg-red-700">Menu utama</a>
                <a href="{{ route('visualisasi.index') }}" class="block py-2.5 px-4 rounded transition duration-200 hover:bg-red-700">Peta Interaktif</a>
                {{-- Anda bisa menambahkan menu lain di sini --}}
            </nav>
        </aside>

        {{-- 4. Overlay gelap untuk mobile ditambahkan di sini --}}
        <div x-show="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 bg-black opacity-50 z-40 md:hidden" x-cloak></div>

        {{-- 5. Div utama dimodifikasi agar bisa bergeser --}}
        <div class="min-h-screen bg-gray-100 transition-all duration-300 ease-in-out" :class="{'md:ml-64': sidebarOpen}">
            {{-- Navigasi asli Anda tetap di sini --}}
            @include('layouts.navigation')

            {{-- 6. Blok header dimodifikasi untuk menyertakan tombol dan menggunakan @yield --}}
            @hasSection('header')
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 flex items-center">
                        @yield('header')
                    </div>
                </header>
            @endif

            <main>
                {{-- Konten dari halaman anak akan ditampilkan di sini --}}
                @yield('content')
            </main>
        </div>
    </div>

    @yield('scripts')
</body>

</html>