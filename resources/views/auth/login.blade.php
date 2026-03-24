<x-guest-layout>
    <div
        class="max-w-md mx-auto bg-gradient-to-b from-teal-50 to-white min-h-screen flex flex-col items-center justify-center px-5 font-sans">

        <!-- Logo & Heading -->
        <div class="text-center mb-8">
            <img src="{{ asset('images/logo.webp') }}" alt="Keluarga H Solichin"
                class="w-24 h-24 mx-auto mb-4 rounded-full shadow-lg transform hover:scale-105 transition-transform">
            <h1 class="text-3xl font-extrabold text-gray-800">Kas Villa</h1>
            <p class="text-sm text-gray-500 mt-2 font-medium">Keluarga H Solichin - Sistem Pengelolaan Kas</p>
        </div>

        <!-- Session Status -->
        @if ($errors->any())
            <div class="w-full mb-4 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-xl shadow-sm">
                <p class="text-sm font-bold text-red-700">{{ $errors->first() }}</p>
            </div>
        @endif

        <!-- Login Form - Google Only -->
        <div class="w-full">
            <!-- Google Login -->
            <a href="{{ route('google.login') }}"
                class="w-full flex items-center justify-center gap-3 px-6 py-4 border-2 border-gray-300 hover:border-teal-400 shadow-lg text-base font-bold rounded-2xl text-gray-700 bg-white hover:bg-teal-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 transition-all transform active:scale-95">
                <svg class="h-6 w-6" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"
                        fill="#4285F4" />
                    <path
                        d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"
                        fill="#34A853" />
                    <path
                        d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"
                        fill="#FBBC05" />
                    <path
                        d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"
                        fill="#EA4335" />
                </svg>
                Masuk dengan Google
            </a>
        </div>

        <!-- Footer -->
        <div class="mt-8 text-center text-xs text-gray-500 font-medium">
            <p class="flex items-center justify-center gap-2">
                <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Masuk dengan akun Google Anda untuk melanjutkan
            </p>
        </div>
    </div>
</x-guest-layout>