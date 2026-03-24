<x-app-layout>
    <div x-data="{ tab: 'verifikasi' }" class="max-w-md mx-auto bg-gray-50 min-h-screen pb-12 font-sans pt-6 px-5">

        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-extrabold text-gray-800 flex items-center gap-2">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z">                                                                                                         </path>
                    </svg>
                    Panel Admin
                </h1>
                <p class="text-xs text-gray-500 mt-1">Kelola kas & data warga</p>
            </div>
            <a href="{{ route('dashboard') }}"
                class="text-sm bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2 px-4 rounded-full transition-all flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Kembali
            </a>
        </div>

        <!-- TABS NAV -->
        <div class="flex gap-2 p-1 bg-gray-200 rounded-full mb-6 relative z-10">
            <button @click="tab = 'verifikasi'" 
                :class="tab === 'verifikasi' ? 'bg-white shadow-sm text-yellow-600' : 'text-gray-500 hover:text-gray-700'"
                class="flex-1 py-2 text-sm font-extrabold rounded-full transition-all duration-300 ease-out flex items-center justify-center gap-1">
                Verifikasi 
                @if($pendingPayments->count() > 0)
                <span class="bg-red-500 text-white text-[10px] px-1.5 py-0.5 rounded-full">{{ $pendingPayments->count() }}</span>
                @endif
            </button>
            <button @click="tab = 'warga'" 
                :class="tab === 'warga' ? 'bg-white shadow-sm text-teal-600' : 'text-gray-500 hover:text-gray-700'"
                class="flex-1 py-2 text-sm font-extrabold rounded-full transition-all duration-300 ease-out flex items-center justify-center gap-1">
                Kelola Warga
            </button>
        </div>

        @if(session('success'))
            <div
                class="mb-4 bg-green-50 border-l-4 border-green-500 p-4 rounded-r-2xl shadow-sm text-sm font-bold text-green-700">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div
                class="mb-4 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-2xl shadow-sm text-sm font-bold text-red-700">
                {{ session('error') }}
            </div>
        @endif

        <!-- TAB VERIFIKASI -->
        <div x-show="tab === 'verifikasi'" x-transition.opacity class="space-y-4">
            @forelse($pendingPayments as $pending)
                <div class="bg-white rounded-3xl p-5 shadow-sm border border-yellow-200 relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-2 h-full bg-yellow-400"></div>

                    <div class="flex items-center gap-4 mb-4 ml-2">
                        <img src="{{ $pending->user->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode($pending->user->name) . '&color=CA8A04&background=FEF9C3' }}"
                            alt="Profile" class="w-12 h-12 rounded-full object-cover border-2 border-yellow-100 shadow-sm">
                        <div>
                            <p class="font-extrabold text-gray-800 text-lg">{{ $pending->user->name }}</p>
                            <p class="text-xs text-gray-500 font-medium flex items-center gap-1">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00-.447.894l1.349 .808.894-1.49V6z"
                                        clip-rule="evenodd"></path>
                                </svg>
                                Titip bayar {{ \Carbon\Carbon::create(2026, 3)->addMonths($pending->bulan_ke)->translatedFormat('F Y') }}
                            </p>
                        </div>
                    </div>

                    <div
                        class="ml-2 flex justify-between items-center bg-gray-50 p-3 rounded-2xl border border-gray-100 mb-4">
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-wide">Nominal Tunai</span>
                        <span class="font-extrabold text-teal-600 text-lg">Rp
                            {{ number_format($pending->nominal, 0, ',', '.') }}</span>
                    </div>

                    <div class="flex gap-2 ml-2">
                        <form action="{{ route('admin.reject', $pending->id) }}" method="POST" class="w-1/3">
                            @csrf
                            <button type="submit" onclick="return confirm('Yakin ingin menolak tagihan ini?')"
                                class="w-full bg-red-50 hover:bg-red-100 text-red-600 font-bold py-3 rounded-xl transition-colors border border-red-100 text-sm flex items-center justify-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                Tolak
                            </button>
                        </form>

                        <form action="{{ route('admin.approve', $pending->id) }}" method="POST" class="w-2/3">
                            @csrf
                            <button type="submit"
                                class="w-full bg-teal-600 hover:bg-teal-700 active:bg-teal-800 text-white font-extrabold py-3 rounded-xl shadow-md transition-all border border-teal-500 text-sm flex justify-center items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7"></path>
                                </svg>
                                Uang Diterima
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="bg-white p-8 rounded-3xl border border-dashed border-gray-300 text-center shadow-sm">
                    <div class="text-4xl mb-3">🍃</div>
                    <h3 class="font-bold text-gray-700">Antrean Kosong</h3>
                    <p class="text-xs text-gray-500 mt-1">Belum ada warga yang titip uang tunai.</p>
                </div>
            @endforelse
        </div>

        <!-- TAB KELOLA WARGA -->
        <div x-show="tab === 'warga'" x-cloak x-transition.opacity class="space-y-6">
            <!-- Form Tambah Warga -->
            <div class="bg-teal-50 rounded-3xl p-5 border border-teal-200 shadow-sm">
                <h3 class="font-extrabold text-teal-800 mb-3 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Tambah Kepala Keluarga
                </h3>
                <form action="{{ route('admin.addKk') }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label class="text-xs font-bold text-teal-700 ml-1">Nama KK</label>
                        <input type="text" name="name" required placeholder="Bpk. Fulan" class="w-full mt-1 bg-white border border-teal-200 text-gray-800 text-sm rounded-xl focus:ring-teal-500 focus:border-teal-500 p-2.5">
                    </div>
                    <div>
                        <label class="text-xs font-bold text-teal-700 ml-1">Email Dummy/Asli</label>
                        <input type="email" name="email" required placeholder="fulan@contoh.com" class="w-full mt-1 bg-white border border-teal-200 text-gray-800 text-sm rounded-xl focus:ring-teal-500 focus:border-teal-500 p-2.5">
                    </div>
                    <button type="submit" class="w-full bg-teal-600 hover:bg-teal-700 active:bg-teal-800 text-white font-extrabold py-3 rounded-xl shadow-md transition-all border border-teal-500 text-sm">
                        Simpan & Buat 14 Tagihan
                    </button>
                </form>
            </div>

            <!-- List Warga (Akordion) -->
            <h3 class="font-extrabold text-gray-800 text-lg px-2 shadow-sm border-b pb-2">Daftar Warga & Pelunasan Manual</h3>
            <div class="space-y-3">
                @foreach($users as $user)
                <div x-data="{ open: false }" class="bg-white rounded-2xl border shadow-sm overflow-hidden">
                    <div @click="open = !open" class="p-4 flex justify-between items-center cursor-pointer hover:bg-gray-50 transition-colors">
                        <div class="flex items-center gap-3">
                            <img src="{{ $user->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&color=0F766E&background=CCFBF1' }}" alt="Profile" class="w-10 h-10 rounded-full object-cover border border-gray-200">
                            <div>
                                <p class="font-extrabold text-gray-800">{{ $user->name }}</p>
                                <p class="text-xs text-gray-500">{{ $user->pembayarans->where('status','lunas')->count() }} dari 14 Lunas</p>
                            </div>
                        </div>
                        <svg class="w-5 h-5 text-gray-400 transform transition-transform" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>

                    <div x-show="open" x-collapse>
                        <div class="p-4 border-t bg-gray-50 space-y-4">
                            
                            <!-- Hapus Warga -->
                            <form action="{{ route('admin.deleteKk', $user->id) }}" method="POST" class="text-right">
                                @csrf
                                @method('DELETE')
                                <button type="submit" onclick="return confirm('YAKIN HAPUS {{ $user->name }}? Seluruh riwayat dan tagihannya akan terhapus selamanya!')" class="text-xs bg-red-100 text-red-600 font-bold px-3 py-1.5 rounded-lg border border-red-200 hover:bg-red-200">
                                    Hapus Keluarga Ini
                                </button>
                            </form>

                            <!-- List Tagihan -->
                            <div class="space-y-2">
                                <p class="text-xs font-extrabold text-gray-400 uppercase tracking-widest pl-1">Buku Kas</p>
                                @foreach($user->pembayarans->sortBy('bulan_ke') as $bayar)
                                <div class="flex justify-between items-center bg-white p-2.5 rounded-xl border {{ $bayar->status === 'lunas' ? 'border-green-200 shadow-sm' : 'border-gray-200' }}">
                                    <div>
                                        <p class="text-sm font-bold text-gray-700">Bln ke-{{ $bayar->bulan_ke }}</p>
                                        <p class="text-[10px] uppercase text-gray-400 font-extrabold">{{ \Carbon\Carbon::create(2026, 3)->addMonths((int)$bayar->bulan_ke)->translatedFormat('F Y') }}</p>
                                    </div>
                                    
                                    @if($bayar->status === 'lunas')
                                        <span class="text-xs bg-green-100 text-green-700 font-extrabold px-2 py-1 rounded-full border border-green-200">LUNAS</span>
                                    @elseif($bayar->status === 'proses')
                                        <span class="text-xs bg-yellow-100 text-yellow-700 font-extrabold px-2 py-1 rounded-full border border-yellow-200 animate-pulse">PROSES</span>
                                    @else
                                        <!-- Pelunasan Manual / Paksa -->
                                        <form action="{{ route('admin.manualPay', $bayar->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" onclick="return confirm('Bayar lunas bulan ke-{{ $bayar->bulan_ke }} untuk {{ $user->name }}?')" class="text-xs bg-gray-800 hover:bg-black text-white px-3 py-1.5 rounded-lg font-bold shadow-md transition-all active:scale-95">
                                                Bayar By Admin
                                            </button>
                                        </form>
                                    @endif
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            
        </div>
    </div>
</x-app-layout>
