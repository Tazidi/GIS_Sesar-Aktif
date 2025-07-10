<nav class="bg-white border-b border-gray-200 shadow-sm">
    <div class="px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex space-x-8">

                <a href="{{ route('home') }}"
                   class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium
                          {{ request()->routeIs('home') ? 'border-red-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }}">
                    Home
                </a>

                <a href="{{ route('visualisasi.index') }}"
                   class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium
                          {{ request()->routeIs('visualisasi.index') ? 'border-red-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }}">
                    Visualisasi Peta
                </a>

                <a href="#"
                   class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700">
                    Galeri
                </a>

                <a href="{{ route('artikel.publik') }}"
                   class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium
                          {{ request()->routeIs('artikel.publik') ? 'border-red-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }}">
                    Artikel
                </a>

                @auth
                    @if(auth()->user()->role === 'admin')
                        <a href="{{ route('admin.index') }}"
                           class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium
                                  {{ request()->routeIs('admin.index') ? 'border-red-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }}">
                            Admin Dashboard
                        </a>
                    @elseif(auth()->user()->role === 'editor')
                        <a href="{{ route('editor.index') }}"
                           class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium
                                  {{ request()->routeIs('editor.index') ? 'border-red-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }}">
                            Editor Dashboard
                        </a>
                    @endif
                @endauth

            </div>

            <div class="flex items-center space-x-4">
                <button class="text-gray-500 hover:text-gray-800">
                    <i class="fas fa-search"></i>
                </button>
                <button class="text-gray-500 hover:text-gray-800">
                    <i class="fas fa-th"></i>
                </button>
            </div>
        </div>
    </div>
</nav>