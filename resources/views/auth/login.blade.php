<x-guest-layout>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button class="ms-3">
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>
    
    <div class="my-4 flex items-center before:mt-0.5 before:flex-1 before:border-t before:border-gray-300 after:mt-0.5 after:flex-1 after:border-t after:border-gray-300">
        <p class="mx-4 mb-0 text-center font-semibold text-gray-500">
            Atau
        </p>
    </div>

    <a href="{{ route('auth.google.redirect') }}" 
       class="w-full flex items-center justify-center gap-2 rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600">
        <svg class="h-5 w-5" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20">
            <path d="M6.29 5.242c0-.62.055-1.214.16-1.786H.5v3.385h3.497c-.155.57-.49 1.29-.982 1.95v2.185h2.78c1.625-1.49 2.56-3.73 2.56-6.242z" />
            <path d="M12.29 10c1.786 0 3.25-.586 4.329-1.571l-2.78-2.186c-.585.393-1.342.62-2.134.62-1.628 0-3.007-1.1-3.5-2.586H.5v2.27A6.47 6.47 0 006.29 10z" />
            <path d="M6.29 10c-1.628 0-3.007-1.1-3.5-2.586H.5v2.27A6.47 6.47 0 006.29 10z" fill-opacity="0.1" />
            <path d="M12.29 10c1.786 0 3.25-.586 4.329-1.571l-2.78-2.186c-.585.393-1.342.62-2.134.62a6.451 6.451 0 01-4.148-1.488L.5 6.414v2.27A6.47 6.47 0 006.29 10z" fill-opacity="0.1" />
            <path d="M12.29 10a6.451 6.451 0 01-4.148-1.488L.5 6.414v2.27A6.47 6.47 0 006.29 10z" fill-opacity="0.1" />
            <path d="M12.29 10c1.786 0 3.25-.586 4.329-1.571l-2.78-2.186c-.585.393-1.342.62-2.134.62-1.628 0-3.007-1.1-3.5-2.586H.5v2.27A6.47 6.47 0 006.29 10z" fill-opacity="0.1" />
            <path d="M13.5 13.5c-1.643 1.272-3.73 2.015-6.29 2.015-3.497 0-6.57-1.8-8.5-4.515L.5 8.73v2.185a9.96 9.96 0 008.5 5.085c2.115 0 4.015-.65 5.615-1.755l-2.78-2.185a4.482 4.482 0 01-1.335.24z" />
        </svg>
        <span>Login dengan Google</span>
    </a>

</x-guest-layout>