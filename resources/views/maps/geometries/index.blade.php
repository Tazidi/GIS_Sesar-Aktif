@extends('layouts.app')

@section('title', 'Kelola Geometri - ' . $map->name)

@section('content')
<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    {{-- Breadcrumb --}}
    <div class="mb-5">
        <nav class="flex" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                        </svg>
                        Dashboard
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <a href="{{ route('maps.index') }}" class="ml-1 text-sm font-medium text-gray-700 hover:text-blue-600 md:ml-2">Maps</a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Kelola Geometri</span>
                    </div>
                </li>
            </ol>
        </nav>
    </div>

    {{-- Header --}}
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Kelola Geometri</h1>
            <p class="text-gray-600 mt-1">Map: <strong>{{ $map->name }}</strong></p>
            @if($map->description)
                <p class="text-sm text-gray-500 mt-1">{{ $map->description }}</p>
            @endif
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('maps.geometries.create', $map) }}"
                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 focus:outline-none focus:border-blue-700 focus:ring focus:ring-blue-200 active:bg-blue-600 disabled:opacity-25 transition">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Tambah Geometri
            </a>
            <a href="{{ route('maps.index') }}"
                class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-500 focus:outline-none focus:border-gray-700 focus:ring focus:ring-gray-200 active:bg-gray-600 disabled:opacity-25 transition">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali ke Maps
            </a>
        </div>
    </div>

    {{-- Success/Error Messages --}}
    @if(session('success'))
        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
            {{ session('error') }}
        </div>
    @endif

    {{-- Grouping Options --}}
    <div class="mb-6 bg-white p-4 rounded-lg shadow border">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <label class="text-sm font-medium text-gray-700">Kelompokkan berdasarkan:</label>
                <select id="groupBy" class="border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="name">Nama</option>
                    <option value="layer">Layer</option>
                    <option value="type">Tipe Geometri</option>
                    <option value="none">Tidak dikelompokkan</option>
                </select>
            </div>
            <div class="flex items-center space-x-2">
                <button id="expandAll" class="text-sm text-blue-600 hover:text-blue-800">Expand All</button>
                <button id="collapseAll" class="text-sm text-gray-600 hover:text-gray-800">Collapse All</button>
            </div>
        </div>
    </div>

    {{-- Geometries List --}}
    <div class="bg-white shadow overflow-hidden sm:rounded-md">
        <div id="geometriesList">
            @php
                // 1. Kelompokkan semua fitur berdasarkan nama terlebih dahulu
                $allGroups = $map->features->groupBy(function($feature) {
                    return $feature->properties['name'] ?? $feature->caption ?? 'Geometri Tanpa Nama';
                });

                // 2. Pisahkan (partition) grup menjadi dua: yang > 3 dan yang <= 3
                [$groupsToDisplay, $smallGroups] = $allGroups->partition(function ($featuresInGroup) {
                    return $featuresInGroup->count() > 3;
                });

                // 3. Ratakan (flatten) grup kecil menjadi satu daftar item individual
                $itemsWithoutGroup = $smallGroups->flatten(1);
            @endphp

            {{-- Tampilkan item individual terlebih dahulu (jika ada) --}}
            @if($itemsWithoutGroup->isNotEmpty())
                <ul class="divide-y divide-gray-200">
                    @foreach($itemsWithoutGroup as $feature)
                        @php
                            $properties = $feature->properties ?? [];
                            $technicalInfo = $feature->technical_info ?? [];
                            $layerNames = $feature->layers->pluck('nama_layer')->implode(', ');
                            $geometryType = $properties['geometry_type'] ?? $technicalInfo['geometry_type'] ?? 'marker';
                        @endphp
                        {{-- Ini adalah item tanpa folder/grup --}}
                        <li> 
                            <div class="px-4 py-4 flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        @if($technicalInfo['icon_url'] ?? false)
                                            <img src="{{ asset($technicalInfo['icon_url']) }}" alt="Icon" class="h-8 w-8 rounded-full">
                                        @else
                                            <div class="h-8 w-8 rounded-full bg-gray-300 flex items-center justify-center">
                                                @switch($geometryType)
                                                    @case('marker')
                                                        <svg class="h-4 w-4 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                                                        </svg>
                                                        @break
                                                @endswitch
                                            </div>
                                        @endif
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $properties['name'] ?? $feature->caption ?? 'Geometri #' . $feature->id }}
                                        </div>
                                        @if($properties['description'] ?? false)
                                            <div class="text-sm text-gray-500">{{ $properties['description'] }}</div>
                                        @endif
                                        <div class="text-xs text-gray-400 mt-1">
                                            Layer: <span class="font-medium">{{ !empty($layerNames) ? $layerNames : 'Tidak ada' }}</span> | 
                                            Tipe: <span class="font-medium">{{ ucfirst($geometryType) }}</span>
                                            {{-- Logika koordinat bisa ditambahkan kembali di sini jika perlu --}}
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('maps.geometries.edit', [$map, $feature]) }}"
                                    class="inline-flex items-center px-3 py-1 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        Edit
                                    </a>
                                    <form action="{{ route('maps.geometries.destroy', [$map, $feature]) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="inline-flex items-center px-3 py-1 border border-red-300 shadow-sm text-xs font-medium rounded text-red-700 bg-white hover:bg-red-50"
                                                onclick="return confirm('Apakah Anda yakin ingin menghapus geometri ini?')">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif

            {{-- Tampilkan grup yang valid (lebih dari 3 item) --}}
            @foreach($groupsToDisplay as $groupName => $features)
                <div class="group-container border-b border-gray-200 last:border-b-0">
                    {{-- Group Header --}}
                    <div class="group-header px-4 py-3 bg-gray-50 hover:bg-gray-100 transition-colors">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center cursor-pointer flex-grow" onclick="toggleGroup('group-{{ $loop->index }}')">
                                <svg class="w-4 h-4 mr-2 text-gray-500 transform transition-transform group-icon" 
                                     id="icon-group-{{ $loop->index }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"></path>
                                    </svg>
                                    <span class="font-medium text-gray-900">{{ $groupName }}</span>
                                    <span class="ml-2 text-sm text-gray-500">({{ $features->count() }} item)</span>
                                </div>
                            </div>
                            <div class="flex items-center space-x-3">
                                <span class="text-xs text-gray-400 hidden sm:inline">Klik kiri untuk expand/collapse</span>
                                {{-- Tombol Hapus Grup --}}
                                <form action="{{ route('maps.geometries.bulkDestroy', $map) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    @foreach($features as $feature)
                                        <input type="hidden" name="feature_ids[]" value="{{ $feature->id }}">
                                    @endforeach
                                    <button type="submit"
                                            class="inline-flex items-center px-2 py-1 border border-red-300 shadow-sm text-xs font-medium rounded text-red-700 bg-white hover:bg-red-50"
                                            onclick="return confirm('Anda yakin ingin menghapus grup \'{{ $groupName }}\' beserta seluruh isinya?')">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        Hapus Grup
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Group Content --}}
                    <div class="group-content hidden" id="group-{{ $loop->index }}">
                        <ul class="divide-y divide-gray-200">
                            @foreach($features as $feature)
                                @php
                                    $properties = $feature->properties ?? [];
                                    $technicalInfo = $feature->technical_info ?? [];
                                    $layerNames = $feature->layers->pluck('nama_layer')->implode(', ');
                                    $geometryType = $properties['geometry_type'] ?? $technicalInfo['geometry_type'] ?? 'marker';
                                @endphp
                                <li class="pl-8">
                                    <div class="px-4 py-4 flex items-center justify-between">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0">
                                                @if($technicalInfo['icon_url'] ?? false)
                                                    <img src="{{ asset($technicalInfo['icon_url']) }}" alt="Icon" class="h-8 w-8 rounded-full">
                                                @else
                                                    <div class="h-8 w-8 rounded-full bg-gray-300 flex items-center justify-center">
                                                        @switch($geometryType)
                                                            @case('marker')
                                                                <svg class="h-4 w-4 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                                                                </svg>
                                                                @break
                                                            @default
                                                                <svg class="h-4 w-4 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                                                                    <circle cx="10" cy="10" r="4"></circle>
                                                                </svg>
                                                        @endswitch
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $properties['name'] ?? $feature->caption ?? 'Geometri #' . $feature->id }}
                                                </div>
                                                @if($properties['description'] ?? false)
                                                    <div class="text-sm text-gray-500">{{ $properties['description'] }}</div>
                                                @endif
                                                <div class="text-xs text-gray-400 mt-1">
                                                    Layer: <span class="font-medium">{{ !empty($layerNames) ? $layerNames : 'Tidak ada' }}</span> | 
                                                    Tipe: <span class="font-medium">{{ ucfirst($geometryType) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <a href="{{ route('maps.geometries.edit', [$map, $feature]) }}"
                                            class="inline-flex items-center px-3 py-1 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                                Edit
                                            </a>
                                            <form action="{{ route('maps.geometries.destroy', [$map, $feature]) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="inline-flex items-center px-3 py-1 border border-red-300 shadow-sm text-xs font-medium rounded text-red-700 bg-white hover:bg-red-50"
                                                        onclick="return confirm('Apakah Anda yakin ingin menghapus geometri ini?')">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                    Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Available Layers Info --}}
    <div class="mt-8 bg-blue-50 border border-blue-200 rounded-md p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800">
                    Tersedia {{ $layers->count() }} layer untuk dipilih
                </h3>
                <div class="mt-2 text-sm text-blue-700">
                    <p>Layer yang tersedia: 
                        @foreach($layers as $layer)
                            <span class="font-medium">{{ $layer->nama_layer }}</span>@if(!$loop->last), @endif
                        @endforeach
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Data untuk regrouping
const bulkDeleteUrl = @json(route('maps.geometries.bulkDestroy', $map));
const csrfToken = @json(csrf_token());

const featuresData = [
    @foreach($map->features as $feature)
        @php
            $properties = $feature->properties ?? [];
            $technicalInfo = $feature->technical_info ? json_decode($feature->technical_info, true) : [];
            $layerNamesForJs = $feature->layers->pluck('nama_layer')->implode(', ');
            $geometryType = $properties['geometry_type'] ?? $technicalInfo['geometry_type'] ?? 'marker';
            
            $coordinates = '';
            if ($feature->geometry && isset($feature->geometry['coordinates'])) {
                if ($geometryType === 'marker' || $geometryType === 'circle') {
                    $coordinates = $feature->geometry['coordinates'][1] . ', ' . $feature->geometry['coordinates'][0];
                } else {
                    $coordinates = count($feature->geometry['coordinates']) . ' titik';
                }
            }
        @endphp
        {
            id: {{ $feature->id }},
            name: @json($properties['name'] ?? $feature->caption ?? 'Geometri #' . $feature->id),
            description: @json($properties['description'] ?? ''),
            layer: @json(!empty($layerNamesForJs) ? $layerNamesForJs : 'Tidak ada layer'),
            type: @json(ucfirst($geometryType)),
            coordinates: @json($coordinates),
            icon_url: @json(isset($technicalInfo['icon_url']) ? asset($technicalInfo['icon_url']) : false),
            edit_url: @json(route('maps.geometries.edit', [$map, $feature])),
            delete_url: @json(route('maps.geometries.destroy', [$map, $feature]))
        }@if(!$loop->last),@endif
    @endforeach
];

// Toggle group function
function toggleGroup(groupId) {
    const content = document.getElementById(groupId);
    const icon = document.getElementById('icon-' + groupId);
    if (!content) return; // guard

    const willShow = content.classList.contains('hidden');
    content.classList.toggle('hidden', !willShow);
    if (icon) {
        icon.style.transform = willShow ? 'rotate(90deg)' : 'rotate(0deg)';
    }
}

// Expand all groups
function expandAll() {
    document.querySelectorAll('.group-content').forEach(content => {
        content.classList.remove('hidden');
    });
    document.querySelectorAll('.group-icon').forEach(icon => {
        icon.style.transform = 'rotate(90deg)';
    });
}

// Collapse all groups
function collapseAll() {
    document.querySelectorAll('.group-content').forEach(content => {
        content.classList.add('hidden');
    });
    document.querySelectorAll('.group-icon').forEach(icon => {
        icon.style.transform = 'rotate(0deg)';
    });
}

// Regroup features based on selected criteria
function regroupFeatures(groupBy) {
    const container = document.getElementById('geometriesList');
    
    if (groupBy === 'none') {
        container.innerHTML = generateFlatList(featuresData); // Pass all data
        return;
    }
    
    // Group features
    const grouped = {};
    featuresData.forEach(feature => {
        const key = feature[groupBy];
        if (!grouped[key]) {
            grouped[key] = [];
        }
        grouped[key].push(feature);
    });
    
    let finalHtml = '';
    
    // Jika dikelompokkan berdasarkan nama, terapkan aturan > 3
    if (groupBy === 'name') {
        const groupsToDisplay = {};
        const itemsWithoutGroup = [];

        // Pisahkan antara grup besar dan item individual
        for (const key in grouped) {
            if (grouped[key].length > 3) {
                groupsToDisplay[key] = grouped[key];
            } else {
                // Spread operator (...) untuk menggabungkan array
                itemsWithoutGroup.push(...grouped[key]);
            }
        }
        
        // Gabungkan HTML dari item individual dan grup besar
        finalHtml = generateFlatList(itemsWithoutGroup) + generateGroupedList(groupsToDisplay);

    } else {
        // Jika dikelompokkan berdasarkan yang lain (Layer/Tipe), tidak ada aturan khusus
        finalHtml = generateGroupedList(grouped);
    }
    
    // Generate grouped HTML
    container.innerHTML = finalHtml;
}

// Generate flat list HTML from a given data array
function generateFlatList(data) {
    if (data.length === 0) return ''; // Return string kosong jika tidak ada data

    return `
        <ul class="divide-y divide-gray-200">
            ${data.map(feature => `
                <li>
                    <div class="px-4 py-4 flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                ${feature.icon_url ? 
                                    `<img src="${feature.icon_url}" alt="Icon" class="h-8 w-8 rounded-full">` :
                                    `<div class="h-8 w-8 rounded-full bg-gray-300 flex items-center justify-center">
                                        <svg class="h-4 w-4 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>`
                                }
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900">${feature.name}</div>
                                ${feature.description ? `<div class="text-sm text-gray-500">${feature.description}</div>` : ''}
                                <div class="text-xs text-gray-400 mt-1">
                                    Layer: <span class="font-medium">${feature.layer}</span> | 
                                    Tipe: <span class="font-medium">${feature.type}</span>
                                    ${feature.coordinates ? `| ${feature.coordinates}` : ''}
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <a href="${feature.edit_url}" class="inline-flex items-center px-3 py-1 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50">
                                Edit
                            </a>
                            <form action="${feature.delete_url}" method="POST" class="inline">
                                <input type="hidden" name="_token" value="${csrfToken}">
                                <input type="hidden" name="_method" value="DELETE">
                                <button type="submit" class="inline-flex items-center px-3 py-1 border border-red-300 shadow-sm text-xs font-medium rounded text-red-700 bg-white hover:bg-red-50" onclick="return confirm('Apakah Anda yakin ingin menghapus geometri ini?')">
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                </li>
            `).join('')}
        </ul>
    `;
}

// Generate grouped list HTML
function generateGroupedList(grouped) {
    let groupIndex = 0;
    return Object.entries(grouped).map(([groupName, features]) => {
        const currentIndex = groupIndex++;
        const featureIdsInputs = features.map(f => `<input type="hidden" name="feature_ids[]" value="${f.id}">`).join('');
        
        return `
            <div class="group-container border-b border-gray-200 last:border-b-0">
                <div class="group-header px-4 py-3 bg-gray-50 hover:bg-gray-100 transition-colors">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center cursor-pointer flex-grow" onclick="toggleGroup('group-${currentIndex}')">
                            <svg class="w-4 h-4 mr-2 text-gray-500 transform transition-transform group-icon" 
                                 id="icon-group-${currentIndex}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-2 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"></path>
                                </svg>
                                <span class="font-medium text-gray-900">${groupName}</span>
                                <span class="ml-2 text-sm text-gray-500">(${features.length} item)</span>
                            </div>
                        </div>
                        <div class="flex items-center space-x-3">
                            <span class="text-xs text-gray-400 hidden sm:inline">Klik kiri untuk expand/collapse</span>
                            <form action="${bulkDeleteUrl}" method="POST" class="inline">
                                <input type="hidden" name="_token" value="${csrfToken}">
                                <input type="hidden" name="_method" value="DELETE">
                                ${featureIdsInputs}
                                <button type="submit"
                                        class="inline-flex items-center px-2 py-1 border border-red-300 shadow-sm text-xs font-medium rounded text-red-700 bg-white hover:bg-red-50"
                                        onclick="return confirm('Anda yakin ingin menghapus grup \\'${groupName}\\' beserta seluruh isinya?')">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    Hapus Grup
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="group-content hidden" id="group-${currentIndex}">
                    <ul class="divide-y divide-gray-200">
                        ${features.map(feature => `
                            <li class="pl-8">
                                <div class="px-4 py-4 flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0">
                                            ${feature.icon_url ? 
                                                `<img src="${feature.icon_url}" alt="Icon" class="h-8 w-8 rounded-full">` :
                                                `<div class="h-8 w-8 rounded-full bg-gray-300 flex items-center justify-center">
                                                    <svg class="h-4 w-4 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                                                    </svg>
                                                </div>`
                                            }
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">${feature.name}</div>
                                            ${feature.description ? `<div class="text-sm text-gray-500">${feature.description}</div>` : ''}
                                            <div class="text-xs text-gray-400 mt-1">
                                                Layer: <span class="font-medium">${feature.layer}</span> | 
                                                Tipe: <span class="font-medium">${feature.type}</span>
                                                ${feature.coordinates ? `| ${feature.coordinates}` : ''}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <a href="${feature.edit_url}" class="inline-flex items-center px-3 py-1 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                            Edit
                                        </a>
                                        <form action="${feature.delete_url}" method="POST" class="inline">
                                            <input type="hidden" name="_token" value="${csrfToken}">
                                            <input type="hidden" name="_method" value="DELETE">
                                            <button type="submit" class="inline-flex items-center px-3 py-1 border border-red-300 shadow-sm text-xs font-medium rounded text-red-700 bg-white hover:bg-red-50" onclick="return confirm('Apakah Anda yakin ingin menghapus geometri ini?')">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </li>
                        `).join('')}
                    </ul>
                </div>
            </div>
        `;
    }).join('');
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    const groupBySelect = document.getElementById('groupBy');
    const expandAllBtn = document.getElementById('expandAll');
    const collapseAllBtn = document.getElementById('collapseAll');
    
    groupBySelect.addEventListener('change', function() {
        regroupFeatures(this.value);
    });
    
    expandAllBtn.addEventListener('click', expandAll);
    collapseAllBtn.addEventListener('click', collapseAll);
});
</script>
@endsection