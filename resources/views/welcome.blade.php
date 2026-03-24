<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Kas Villa') }} - Sistem Pembayaran Keluarga</title>
    
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&family=poppins:600,700,800&display=swap" rel="stylesheet" />
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        * {
            font-family: 'Figtree', sans-serif;
        }
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
        }
    </style>
</head>

<body class="font-sans antialiased bg-gradient-to-b from-slate-50 via-blue-50 to-teal-50 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900 overflow-x-hidden">
    <!-- Navigation Bar -->
    <nav class="fixed top-0 w-full z-50 bg-white/80 dark:bg-slate-900/80 backdrop-blur-md border-b border-gray-200/20 dark:border-gray-700/20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center gap-2">
                    <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-teal-500 to-emerald-500 flex items-center justify-center">
                        <span class="text-white font-bold text-lg">💰</span>
                    </div>
                    <span class="font-bold text-xl text-gray-900 dark:text-white">Kas Villa</span>
                </div>
                
                <div class="flex items-center gap-4">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="px-6 py-2 bg-gradient-to-r from-teal-600 to-emerald-600 text-white rounded-lg font-semibold hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="px-6 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors duration-300 font-medium">
                            Login
                        </a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="px-6 py-2 bg-gradient-to-r from-teal-600 to-emerald-600 text-white rounded-lg font-semibold hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
                                Register
                            </a>
                        @endif
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section with Parallax -->
    <section class="relative min-h-screen flex items-center justify-center pt-20 overflow-hidden">
        <!-- Animated Background Elements -->
        <div class="absolute top-20 right-10 w-72 h-72 bg-gradient-to-br from-teal-400 to-blue-400 rounded-full blur-3xl opacity-20 animate-float" data-parallax="0.5"></div>
        <div class="absolute bottom-20 left-10 w-72 h-72 bg-gradient-to-tr from-emerald-400 to-teal-400 rounded-full blur-3xl opacity-20 animate-float-sm" data-parallax="0.3"></div>
        
        <!-- Grid Pattern -->
        <div class="absolute inset-0 opacity-5" data-parallax="0.2">
            <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <pattern id="hero-grid" x="0" y="0" width="40" height="40" patternUnits="userSpaceOnUse">
                        <path d="M 40 0 L 0 0 0 40" fill="none" stroke="currentColor" stroke-width="0.5" />
                    </pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#hero-grid)" />
            </svg>
        </div>

        <div class="relative z-10 max-w-4xl mx-auto px-4 text-center">
            <div class="animate-fade-in-down" data-animate>
                <span class="inline-block px-4 py-2 bg-teal-100 dark:bg-teal-900/30 text-teal-700 dark:text-teal-300 rounded-full text-sm font-bold mb-6 border border-teal-200 dark:border-teal-800">
                    ✨ Transparansi & Kemudahan
                </span>
            </div>

            <h1 class="text-5xl md:text-7xl font-bold mb-6 animate-fade-in-up stagger-1" data-animate>
                <span class="bg-gradient-to-r from-teal-600 via-emerald-500 to-blue-600 bg-clip-text text-transparent">
                    Kelola Kas Keluarga
                </span>
                <br>
                <span class="text-gray-900 dark:text-white">Dengan Mudah & Aman</span>
            </h1>

            <p class="text-xl md:text-2xl text-gray-600 dark:text-gray-300 mb-8 max-w-2xl mx-auto animate-fade-in-up stagger-2" data-animate>
                Platform digital untuk manajemen kas keluarga yang transparan, aman, dan mudah digunakan. Bayar kapan saja, kemana saja.
            </p>

            <div class="flex flex-col sm:flex-row gap-4 justify-center mb-12 animate-fade-in-up stagger-3" data-animate>
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="px-8 py-4 bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white font-bold rounded-xl shadow-lg hover:shadow-2xl hover:-translate-y-2 transition-all duration-300 flex items-center justify-center gap-2">
                        <span>Mulai Sekarang</span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>
                @endif
                <a href="#features" class="px-8 py-4 bg-white dark:bg-slate-800 text-gray-900 dark:text-white font-bold rounded-xl shadow-lg hover:shadow-xl hover:-translate-y-2 transition-all duration-300 border-2 border-gray-200 dark:border-gray-700 flex items-center justify-center gap-2">
                    <span>Pelajari Lebih Lanjut</span>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                    </svg>
                </a>
            </div>

            <!-- Features Preview -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-16 animate-fade-in-up stagger-4" data-animate>
                <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-lg border border-gray-200 dark:border-gray-700 hover:shadow-xl hover:-translate-y-2 transition-all duration-300">
                    <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-teal-400 to-teal-600 text-white flex items-center justify-center mb-4 text-2xl">📊</div>
                    <h3 class="font-bold text-lg mb-2 text-gray-900 dark:text-white">Transparan</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">Lihat laporan keuangan keluarga secara real-time</p>
                </div>

                <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-lg border border-gray-200 dark:border-gray-700 hover:shadow-xl hover:-translate-y-2 transition-all duration-300">
                    <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-emerald-400 to-emerald-600 text-white flex items-center justify-center mb-4 text-2xl">🔒</div>
                    <h3 class="font-bold text-lg mb-2 text-gray-900 dark:text-white">Aman</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">Data terlindungi dengan enkripsi tingkat tinggi</p>
                </div>

                <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-lg border border-gray-200 dark:border-gray-700 hover:shadow-xl hover:-translate-y-2 transition-all duration-300">
                    <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-blue-400 to-blue-600 text-white flex items-center justify-center mb-4 text-2xl">⚡</div>
                    <h3 class="font-bold text-lg mb-2 text-gray-900 dark:text-white">Cepat</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">Proses pembayaran instan dan mudah digunakan</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="relative py-20 px-4">
        <div class="max-w-6xl mx-auto">
            <div class="text-center mb-16">
                <h2 class="text-4xl md:text-5xl font-bold mb-4 text-gray-900 dark:text-white" data-animate>
                    Fitur Unggulan
                </h2>
                <p class="text-xl text-gray-600 dark:text-gray-400" data-animate>
                    Semua yang Anda butuhkan untuk mengelola kas keluarga dengan sempurna
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center mb-20">
                <div data-animate class="animate-fade-in-left">
                    <h3 class="text-3xl font-bold mb-6 text-gray-900 dark:text-white">
                        Pembayaran Mudah
                    </h3>
                    <ul class="space-y-4">
                        <li class="flex items-center gap-4">
                            <div class="w-8 h-8 rounded-full bg-teal-100 dark:bg-teal-900 text-teal-600 dark:text-teal-300 flex items-center justify-center font-bold">✓</div>
                            <span class="text-gray-700 dark:text-gray-300">Bayar online melalui Duitku</span>
                        </li>
                        <li class="flex items-center gap-4">
                            <div class="w-8 h-8 rounded-full bg-teal-100 dark:bg-teal-900 text-teal-600 dark:text-teal-300 flex items-center justify-center font-bold">✓</div>
                            <span class="text-gray-700 dark:text-gray-300">Bayar tunai kepada admin</span>
                        </li>
                        <li class="flex items-center gap-4">
                            <div class="w-8 h-8 rounded-full bg-teal-100 dark:bg-teal-900 text-teal-600 dark:text-teal-300 flex items-center justify-center font-bold">✓</div>
                            <span class="text-gray-700 dark:text-gray-300">Verifikasi otomatis & manual</span>
                        </li>
                        <li class="flex items-center gap-4">
                            <div class="w-8 h-8 rounded-full bg-teal-100 dark:bg-teal-900 text-teal-600 dark:text-teal-300 flex items-center justify-center font-bold">✓</div>
                            <span class="text-gray-700 dark:text-gray-300">Notifikasi real-time untuk setiap transaksi</span>
                        </li>
                    </ul>
                </div>
                <div class="bg-gradient-to-br from-teal-500 to-emerald-500 rounded-3xl p-8 shadow-2xl" data-parallax="0.3">
                    <div class="aspect-square bg-white rounded-2xl flex items-center justify-center text-6xl">
                        💳
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
                <div class="bg-gradient-to-br from-blue-500 to-indigo-500 rounded-3xl p-8 shadow-2xl order-2 md:order-1" data-parallax="0.2">
                    <div class="aspect-square bg-white rounded-2xl flex items-center justify-center text-6xl">
                        📈
                    </div>
                </div>
                <div class="order-1 md:order-2" data-animate class="animate-fade-in-right">
                    <h3 class="text-3xl font-bold mb-6 text-gray-900 dark:text-white">
                        Laporan Lengkap
                    </h3>
                    <ul class="space-y-4">
                        <li class="flex items-center gap-4">
                            <div class="w-8 h-8 rounded-full bg-teal-100 dark:bg-teal-900 text-teal-600 dark:text-teal-300 flex items-center justify-center font-bold">✓</div>
                            <span class="text-gray-700 dark:text-gray-300">Pantau progress target dana</span>
                        </li>
                        <li class="flex items-center gap-4">
                            <div class="w-8 h-8 rounded-full bg-teal-100 dark:bg-teal-900 text-teal-600 dark:text-teal-300 flex items-center justify-center font-bold">✓</div>
                            <span class="text-gray-700 dark:text-gray-300">Riwayat lengkap setiap transaksi</span>
                        </li>
                        <li class="flex items-center gap-4">
                            <div class="w-8 h-8 rounded-full bg-teal-100 dark:bg-teal-900 text-teal-600 dark:text-teal-300 flex items-center justify-center font-bold">✓</div>
                            <span class="text-gray-700 dark:text-gray-300">Export laporan ke PDF & Excel</span>
                        </li>
                        <li class="flex items-center gap-4">
                            <div class="w-8 h-8 rounded-full bg-teal-100 dark:bg-teal-900 text-teal-600 dark:text-teal-300 flex items-center justify-center font-bold">✓</div>
                            <span class="text-gray-700 dark:text-gray-300">Dashboard yang mudah dipahami</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="relative py-20 px-4 overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-r from-teal-600 to-emerald-600 opacity-90" data-parallax="0.2"></div>
        <div class="absolute inset-0 opacity-10">
            <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <pattern id="cta-dots" x="0" y="0" width="50" height="50" patternUnits="userSpaceOnUse">
                        <circle cx="25" cy="25" r="2" fill="white" />
                    </pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#cta-dots)" />
            </svg>
        </div>

        <div class="relative z-10 max-w-4xl mx-auto text-center">
            <h2 class="text-4xl md:text-5xl font-bold text-white mb-6" data-animate>
                Siap Mengelola Kas Keluarga?
            </h2>
            <p class="text-xl text-teal-50 mb-8" data-animate>
                Bergabunglah dengan keluarga besar kami dan mulai transparansi keuangan hari ini
            </p>
            @if (Route::has('register'))
                <a href="{{ route('register') }}" class="inline-block px-8 py-4 bg-white text-teal-600 font-bold rounded-xl shadow-lg hover:shadow-2xl hover:-translate-y-2 transition-all duration-300 border-2 border-white hover:bg-teal-50">
                    Daftar Sekarang - Gratis! 🚀
                </a>
            @endif
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 dark:bg-black text-gray-300 py-12 px-4">
        <div class="max-w-6xl mx-auto">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
                <div>
                    <div class="flex items-center gap-2 mb-4">
                        <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-teal-500 to-emerald-500 flex items-center justify-center">
                            <span class="text-white font-bold text-lg">💰</span>
                        </div>
                        <span class="font-bold text-lg text-white">Kas Villa</span>
                    </div>
                    <p class="text-sm">Solusi digital untuk manajemen kas keluarga yang transparan dan aman.</p>
                </div>

                <div>
                    <h4 class="font-bold text-white mb-4">Navigasi</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#features" class="hover:text-teal-400 transition">Fitur</a></li>
                        <li><a href="{{ route('login') }}" class="hover:text-teal-400 transition">Login</a></li>
                        @if (Route::has('register'))
                            <li><a href="{{ route('register') }}" class="hover:text-teal-400 transition">Daftar</a></li>
                        @endif
                    </ul>
                </div>

                <div>
                    <h4 class="font-bold text-white mb-4">Dukungan</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#" class="hover:text-teal-400 transition">Panduan</a></li>
                        <li><a href="#" class="hover:text-teal-400 transition">FAQ</a></li>
                        <li><a href="#" class="hover:text-teal-400 transition">Hubungi Kami</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="font-bold text-white mb-4">Kontak</h4>
                    <p class="text-sm">Kel H Solichin<br>Tangerang Selatan<br>Indonesia</p>
                </div>
            </div>

            <div class="border-t border-gray-800 pt-8 text-center text-sm">
                <p>&copy; {{ date('Y') }} Kas Villa. Dibuat dengan ❤️ untuk keluarga besar. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Scroll to top button -->
    <button id="scrollTopBtn" class="fixed bottom-8 right-8 w-12 h-12 bg-gradient-to-r from-teal-600 to-emerald-600 text-white rounded-full shadow-lg hover:shadow-xl hover:-translate-y-2 transition-all opacity-0 pointer-events-none duration-300 flex items-center justify-center z-50" onclick="window.scrollTo({ top: 0, behavior: 'smooth' })">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7-7m0 0l-7 7m7-7v8" />
        </svg>
    </button>
</body>
</html>
