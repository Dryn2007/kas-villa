<x-app-layout>
    <div x-data="{ downloadModal: false }" class="max-w-md mx-auto bg-gray-50 min-h-screen pb-12 font-sans">

        <div class="bg-teal-600 text-white p-6 rounded-b-3xl shadow-lg relative overflow-hidden">
            <div class="absolute top-0 right-0 opacity-10">
                <svg class="w-32 h-32 text-white" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M5 3a2 2 0 012-2h6a2 2 0 012 2v2h2a2 2 0 012 2v10a2 2 0 01-2 2H3a2 2 0 01-2-2V7a2 2 0 012-2h2V3z"></path>
                </svg>
            </div>
            <div class="relative z-10">
                <div class="flex items-center gap-2 text-teal-100 text-sm font-medium mb-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10l-2 1m0 0l-2-1m2 1v2.5M20 7l-2 1m2-1l-2-1m2 1v2.5M14 4l-2-1-2 1m2-1v2.5M14 4l2-1m-2 1l-2-1m2 1v2.5"></path>
                    </svg>
                    Selamat datang, {{ Auth::user()->name }}
                </div>
                <h1 class="text-2xl font-bold mb-4">Kas Villa Keluarga</h1>

                <div class="bg-white/20 backdrop-blur-sm rounded-2xl p-4 border border-white/30">
                    <p class="text-sm text-teal-50 mb-1">Total Terkumpul</p>
                    <div class="flex justify-between items-end mb-2">
                        <h2 class="text-2xl font-extrabold">Rp {{ number_format($totalTerkumpul, 0, ',', '.') }}</h2>
                        <span class="text-sm font-medium">/ Rp {{ number_format($targetDana / 1000, 0, ',', '.') }}k</span>
                    </div>
                    <div class="w-full bg-teal-800 rounded-full h-3">
                        <div class="bg-yellow-400 h-3 rounded-full" style="width: {{ $persentase }}%"></div>
                    </div>
                </div>

                <button @click="downloadModal = true" class="mt-4 w-full bg-yellow-400 border border-yellow-300 hover:bg-yellow-500 text-teal-900 font-extrabold py-2.5 px-4 rounded-xl shadow-md transition-all active:scale-95 flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Unduh Laporan Kas
                </button>
            </div>
        </div>

        @if(request()->has('kk_id'))
                            <div class="px-5 mt-6">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                                        <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 12H9m6 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Iuran {{ $selectedKk->name }}
                                    </h3>
                                    <a href="{{ route('dashboard') }}"
                                        class="text-sm bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-1 px-3 rounded-full transition-all flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                        </svg>
                                        Kembali
                                    </a>
                                </div>

                                @if(session('success'))
                                    <div x-data="{ show: true }" x-show="show"
                                        class="mb-4 bg-green-50 border-l-4 border-green-500 p-4 rounded-r-2xl shadow-sm flex items-center justify-between transition-all">
                                        <div class="flex items-center gap-3">
                                            <span class="text-green-700 font-bold text-sm">{{ session('success') }}</span>
                                        </div>
                                        <button @click="show = false" class="text-green-600 hover:text-green-800 font-bold text-lg">✕</button>
                                    </div>
                                @endif

                                @if(session('error'))
                                    <div x-data="{ show: true }" x-show="show"
                                        class="mb-4 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-2xl shadow-sm flex items-center justify-between transition-all">
                                        <div class="flex items-center gap-3">
                                            <span class="text-red-700 font-bold text-sm">{{ session('error') }}</span>
                                        </div>
                                        <button @click="show = false" class="text-red-600 hover:text-red-800 font-bold text-lg">✕</button>
                                    </div>
                                @endif

                                @php
            $payableTagihans = $tagihanKk->whereNotIn('status', ['lunas', 'proses'])->values();
            $payableIds = $payableTagihans->pluck('id');
                                @endphp

                                <form id="bulkPaymentForm" action="{{ route('dummy.pay.bulk') }}" method="POST" enctype="multipart/form-data" x-data="{
                                    selected: [],
                                    payableIds: {{ $payableIds->toJson() }},
                                    showUpload: false,

                                    submitForm() {
                                        // Validasi file upload jika diperlukan
                                        const fileInput = document.getElementById('bukti_pembayaran');
                                        if (!fileInput.value) {
                                            alert('Silakan upload bukti pembayaran terlebih dahulu!');
                                            return;
                                        }
                                        document.getElementById('bulkPaymentForm').submit();
                                    },                                    toggle(id) {
                                        let idStr = id.toString();
                                        let idx = this.payableIds.indexOf(id);
                                        console.log('Toggle ID:', id, 'Index:', idx);

                                        if (!this.selected.includes(idStr)) {
                                            // Jika dicentang: Centang juga semua yang sebelumnya
                                            for(let i = 0; i <= idx; i++) {
                                                let curr = this.payableIds[i].toString();
                                                if(!this.selected.includes(curr)) this.selected.push(curr);
                                            }
                                        } else {
                                            // Jika dihapus: Hapus ini dan semua yang sesudahnya
                                            for(let i = idx; i < this.payableIds.length; i++) {
                                                let curr = this.payableIds[i].toString();
                                                let pos = this.selected.indexOf(curr);
                                                if(pos !== -1) this.selected.splice(pos, 1);
                                            }
                                        }
                                        console.log('After toggle, selected:', this.selected);
                                        this.showUpload = false;
                                    },

                                    isAllowed(id) {
                                        let idx = this.payableIds.indexOf(id);
                                        if (idx === 0) return true;
                                        let prevId = this.payableIds[idx - 1].toString();
                                        return this.selected.includes(prevId);
                                    }
                                }" class="pb-36"> @csrf

                                    @foreach ($tagihanKk as $tagihan)
                                        @if ($tagihan->status === 'lunas')
                                            <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100 flex justify-between mb-3 opacity-60">
                                                <div class="flex items-center gap-4">
                                                    <div class="bg-green-100 p-2 rounded-full flex items-center justify-center">
                                                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                        </svg>
                                                    </div>
                                                    <div>
                                                        <p class="font-bold text-gray-800">{{ \Carbon\Carbon::create(2026, 3)->addMonths($tagihan->bulan_ke)->translatedFormat('F Y') }}</p>
                                                        <p class="text-xs text-gray-500">Rp {{ number_format($tagihan->nominal, 0, ',', '.') }}</p>
                                                    </div>
                                                </div>
                                                <span class="bg-green-100 text-green-700 py-1 px-3 rounded-full text-xs font-bold border border-green-200 mt-1 h-max">Lunas</span>
                                            </div>

                                        @elseif ($tagihan->status === 'proses')
                                            <div class="bg-white rounded-2xl p-4 shadow-sm border border-yellow-200 flex justify-between mb-3 bg-gradient-to-r from-yellow-50 to-white">
                                                <div class="flex items-center gap-4">
                                                    <div class="bg-yellow-100 p-2 rounded-full flex items-center justify-center animate-pulse">
                                                        <svg class="w-5 h-5 text-yellow-600" fill="currentColor" viewBox="0 0 24 24">
                                                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"></path>
                                                        </svg>
                                                    </div>
                                                    <div>
                                                        <p class="font-bold text-gray-800">{{ \Carbon\Carbon::create(2026, 3)->addMonths($tagihan->bulan_ke)->translatedFormat('F Y') }}</p>
                                                        <p class="text-xs text-gray-500">Rp {{ number_format($tagihan->nominal, 0, ',', '.') }}</p>
                                                    </div>
                                                </div>
                                                <span class="bg-yellow-100 text-yellow-700 py-1 px-3 rounded-full text-xs font-bold border border-yellow-200 mt-1 h-max shadow-sm">Menunggu ACC</span>
                                            </div>

                                        @else
                                            <label class="rounded-2xl p-4 shadow-sm border-l-4 border-l-red-500 flex justify-between items-center mb-3 transition-all cursor-pointer relative"
                                                 :class="{
                                                     'opacity-60 bg-gray-50': !isAllowed({{ $tagihan->id }}),
                                                     'bg-teal-50 border-teal-300': selected.includes('{{ $tagihan->id }}'),
                                                     'bg-white hover:bg-red-50': !selected.includes('{{ $tagihan->id }}') && isAllowed({{ $tagihan->id }})
                                                 }">

                                                <div x-show="!isAllowed({{ $tagihan->id }})" class="absolute inset-0 z-10 cursor-not-allowed"></div>

                                                <div class="flex items-center gap-4">
                                                    <!-- Checkbox hanya muncul jika ada 2+ bulan yang bisa dibayar -->
                                                    <div x-show="payableIds.length >= 2" class="w-6 h-6 rounded border-2 transition-all flex items-center justify-center flex-shrink-0"
                                                        :class="{
                                                            'bg-teal-600 border-teal-600': selected.includes('{{ $tagihan->id }}'),
                                                            'border-gray-300 bg-white': !selected.includes('{{ $tagihan->id }}')
                                                        }">
                                                        <svg x-show="selected.includes('{{ $tagihan->id }}')" class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                                        </svg>
                                                    </div>

                                                    <!-- Hidden checkbox untuk form submission -->
                                                    <input type="checkbox" value="{{ $tagihan->id }}"
                                                        :checked="selected.includes('{{ $tagihan->id }}')"
                                                        @click.prevent="toggle({{ $tagihan->id }})"
                                                        :disabled="!isAllowed({{ $tagihan->id }})"
                                                        class="hidden">

                                                    <div>
                                                        <p class="font-bold text-gray-800 text-lg">{{ \Carbon\Carbon::create(2026, 3)->addMonths($tagihan->bulan_ke)->translatedFormat('F Y') }}</p>
                                                        <p class="text-sm text-gray-500">Rp {{ number_format($tagihan->nominal, 0, ',', '.') }}</p>
                                                    </div>
                                                </div>

                                                <div x-show="!isAllowed({{ $tagihan->id }})" class="text-[10px] text-gray-500 font-bold bg-gray-200 px-2 py-1 rounded flex items-center gap-1">
                                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    Kunci
                                                </div>
                                            </label>
                                        @endif
                                    @endforeach

                                    <div x-show="selected.length > 0"
                                         x-transition:enter="transition ease-out duration-300"
                                         x-transition:enter-start="opacity-0 transform translate-y-12 scale-95"
                                         x-transition:enter-end="opacity-100 transform translate-y-0 scale-100"
                                         x-transition:leave="transition ease-in duration-200"
                                         x-transition:leave-start="opacity-100 transform translate-y-0 scale-100"
                                         x-transition:leave-end="opacity-0 transform translate-y-12 scale-95"
                                         class="fixed bottom-6 left-1/2 transform -translate-x-1/2 w-full max-w-sm px-5 z-50">
                                        <div class="bg-white p-4 rounded-3xl shadow-2xl border-2 border-teal-200 flex flex-col gap-3 backdrop-blur-md bg-white/95">
                                            <p class="text-center text-sm font-extrabold text-gray-700">Tagihan (<span x-text="selected.length" class="text-teal-600"></span> bln):</p>
                                            
                                            <!-- Tombol Awal -->
                                            <button x-show="!showUpload" type="button" @click="showUpload = true" class="w-full bg-teal-600 hover:bg-teal-700 text-white font-extrabold py-3 px-4 rounded-2xl shadow-md flex justify-center items-center gap-2 transition-all duration-200 active:scale-95 border-2 border-teal-500 hover:shadow-lg hover:-translate-y-1">
                                                <svg class="w-5 h-5 mb-0.5 transition-transform duration-200 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                                <span class="text-[13px] uppercase tracking-wide font-bold">Saya Sudah Bayar</span>
                                            </button>

                                            <!-- Form Upload yang muncul setelah tombol ditekan -->
                                            <div x-show="showUpload" x-transition.opacity class="flex flex-col gap-3">
                                                <div>
                                                    <label class="block text-xs font-bold text-gray-700 mb-1">Upload Bukti Transfer / Tunai</label>
                                                    <input type="file" name="bukti_pembayaran" id="bukti_pembayaran" required accept="image/*" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100" />
                                                </div>

                                                <button type="button" @click="submitForm()" class="w-full bg-yellow-400 hover:bg-yellow-500 text-yellow-900 font-extrabold py-3 px-4 rounded-2xl shadow-md flex justify-center items-center gap-2 transition-all duration-200 active:scale-95 border border-yellow-300 hover:shadow-lg hover:-translate-y-1">
                                                    <svg class="w-5 h-5 mb-0.5 transition-transform duration-200 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    <span class="text-[13px] uppercase tracking-wide font-bold">Kirim Bukti Pembayaran</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Hidden inputs untuk selected IDs -->
                                    <template x-for="id in selected" :key="id">
                                        <input type="hidden" name="tagihan_ids[]" :value="id">
                                    </template>
                                </form>
                            </div>

        @else
            <div x-data="{ openModal: false }" class="px-5 mt-6">
                <h3 class="text-lg font-bold text-gray-800 mb-3 flex items-center gap-2">
                    <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Pilih Tagihan</h3>

                <div class="grid grid-cols-1 gap-4">
                    <a href="{{ route('dashboard', ['kk_id' => Auth::user()->id]) }}"
                        class="bg-gradient-to-r from-teal-500 to-teal-600 hover:from-teal-600 hover:to-teal-700 text-white p-5 rounded-2xl shadow-md transform active:scale-95 transition-all flex items-center justify-between border border-teal-400">
                        <div>
                            <p class="text-teal-100 text-xs font-semibold mb-1">Tagihan Utama</p>
                            <h4 class="font-bold text-lg">Keluarga Saya</h4>
                        </div>
                        <div
                            class="w-12 h-12 rounded-full overflow-hidden border-2 border-teal-200 shadow-sm flex-shrink-0">
                            <img src="{{ Auth::user()->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->name) . '&color=0D9488&background=F0FDFA' }}"
                                alt="Profile" class="w-full h-full object-cover">
                        </div>
                    </a>

                    <button @click="openModal = true"
                        class="w-full bg-white hover:bg-teal-50 text-left p-5 rounded-2xl shadow-sm border border-gray-200 transform active:scale-95 transition-all flex items-center justify-between group">
                        <div>
                            <p class="text-gray-500 text-xs font-semibold mb-1 group-hover:text-teal-600 transition-colors">
                                Ada kendala pada saudaramu?</p>
                            <h4 class="font-bold text-gray-800 text-lg group-hover:text-teal-700 transition-colors">Bantu
                                Bayarin Ajaa 🤝</h4>
                        </div>
                        <div
                            class="bg-gray-50 group-hover:bg-teal-100 p-3 rounded-full text-2xl transition-colors border border-gray-100 group-hover:border-teal-200">
                            🎁
                        </div>
                    </button>
                </div>

                <div x-show="openModal" style="display: none;"
                    class="fixed inset-0 z-50 bg-gray-900/60 backdrop-blur-sm flex items-center justify-center p-4"
                    x-transition.opacity>

                    <div x-show="openModal" @click.away="openModal = false"
                        class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden flex flex-col"
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-y-10 scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                        x-transition:leave="transition ease-in duration-200"
                        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                        x-transition:leave-end="opacity-0 translate-y-10 scale-95">

                        <div class="bg-gray-50 px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                            <div>
                                <h3 class="font-extrabold text-gray-800 text-lg">Daftar Keluarga</h3>
                                <p class="text-xs text-gray-500 font-medium">Pilih saudara yang ingin dibantu</p>
                            </div>
                            <button @click="openModal = false"
                                class="bg-gray-200 text-gray-600 hover:bg-red-100 hover:text-red-500 rounded-full w-8 h-8 flex items-center justify-center transition-colors font-bold">
                                ✕
                            </button>
                        </div>

                        <div class="p-6 max-h-[60vh] overflow-y-auto" style="scrollbar-width: thin;">
                            <div class="grid grid-cols-3 gap-3">
                                @foreach($semuaKk as $kk)
                                    @if($kk->id !== Auth::user()->id)
                                        <a href="{{ route('dashboard', ['kk_id' => $kk->id]) }}"
                                            class="bg-white p-3 rounded-2xl border border-gray-200 flex flex-col items-center text-center hover:bg-teal-50 hover:border-teal-400 hover:shadow-md active:scale-95 transition-all group">
                                            @if($kk->avatar)
                                                <img src="{{ $kk->avatar }}" alt="{{ $kk->name }}"
                                                    class="w-12 h-12 rounded-full mb-2 object-cover shadow-inner border border-teal-100 group-hover:border-teal-400 transition-colors">
                                            @else
                                                <div
                                                    class="w-12 h-12 rounded-full bg-teal-50 text-teal-600 flex items-center justify-center font-extrabold mb-2 text-xl shadow-inner border border-teal-100 group-hover:bg-teal-500 group-hover:text-white transition-colors">
                                                    {{ strtoupper(substr($kk->name, 0, 1)) }}
                                                </div>
                                            @endif
                                            <span class="text-[11px] font-bold text-gray-700 leading-tight">{{ $kk->name }}</span>
                                        </a>
                                    @endif
                                @endforeach
                            </div>
                        </div>

                    </div>
                </div>
            </div>


            <div class="px-5 mt-4">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="text-lg font-bold text-gray-800 flex items-center">🕒 Riwayat Bulan Ini</h3>
                    <a href="{{ route('riwayat') }}" class="text-xs font-bold text-teal-600 hover:text-teal-800 bg-teal-50 px-3 py-1 rounded-full border border-teal-100">Lihat Semua</a>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-8">
                    @forelse($historyTerbaru as $history)
                        <div class="p-4 border-b border-gray-50 flex items-center justify-between last:border-0">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-10 h-10 rounded-full overflow-hidden flex-shrink-0 border border-gray-200 shadow-sm">
                                    <img src="{{ $history->user->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode($history->user->name) . '&color=3B82F6&background=EFF6FF' }}"
                                        class="w-full h-full object-cover">
                                </div>
                                <div>
                                    <p class="font-bold text-gray-800 text-sm">{{ $history->user->name }}</p>
                                    <p class="text-xs text-gray-500">Membayar {{ \Carbon\Carbon::create(2026, 3)->addMonths($history->bulan_ke)->translatedFormat('F Y') }}</p>
                                </div>
                            </div>
                            <div class="text-right flex flex-col items-end">
                                <span class="text-xs font-extrabold text-green-600 bg-green-50 px-2 py-0.5 rounded">Lunas</span>
                                <p class="text-[10px] text-gray-400 mt-1">{{ $history->updated_at->diffForHumans() }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="p-6 text-center text-sm text-gray-500 font-medium">Belum ada riwayat pembayaran. Jadi yang
                            pertama!</div>
                    @endforelse
                </div>
            </div>

        @endif

        <!-- Modal Unduh Laporan -->
        <div x-show="downloadModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-gray-900 bg-opacity-50 backdrop-blur-sm" @click="downloadModal = false"></div>

                <div class="relative inline-block w-full max-w-sm p-6 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-2xl sm:my-8"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                    
                    <div class="flex justify-between items-center border-b border-gray-100 pb-3 mb-4">
                        <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                            <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            Unduh Laporan
                        </h3>
                        <button @click="downloadModal = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <form id="downloadForm" method="GET" target="_blank" class="space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Pilih Keluarga (KK)</label>
                            <div class="max-h-40 overflow-y-auto border border-gray-200 rounded-xl p-2 bg-gray-50">
                                <label class="flex items-center gap-2 p-2 hover:bg-teal-50 rounded-lg cursor-pointer transition-colors">
                                    <input type="checkbox" name="kk_ids[]" value="all" checked class="rounded text-teal-600 focus:ring-teal-500 bg-white border-gray-300 w-4 h-4">
                                    <span class="text-sm font-bold text-gray-800">Semua Keluarga</span>
                                </label>
                                <!-- Kita bisa ambil dari global atau controller. Di dashboard sudah ada $semuaKk untuk grid di else condition -->
                                <!-- Khusus jika di dalam menu user $semuaKk mungkin tidak ada (kita pakai App\Models\User::all()) -->
                                @php $users_for_export = \App\Models\User::all(); @endphp
                                @foreach($users_for_export as $u)
                                <label class="flex items-center gap-2 p-2 hover:bg-teal-50 rounded-lg cursor-pointer transition-colors">
                                    <input type="checkbox" name="kk_ids[]" value="{{ $u->id }}" class="rounded text-teal-600 focus:ring-teal-500 bg-white border-gray-300 w-4 h-4 kk-checkbox">
                                    <span class="text-sm font-medium text-gray-700">{{ $u->name }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Dari Bulan</label>
                                <select name="start_bulan" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-sm">
                                    @for($i = 1; $i <= 14; $i++)
                                        <option value="{{ $i }}">{{ \Carbon\Carbon::create(2026, 3)->addMonths($i)->translatedFormat('F Y') }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Sampai Bulan</label>
                                <select name="end_bulan" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-sm">
                                    @for($i = 1; $i <= 14; $i++)
                                        <option value="{{ $i }}" {{ $i == 14 ? 'selected' : '' }}>{{ \Carbon\Carbon::create(2026, 3)->addMonths($i)->translatedFormat('F Y') }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>

                        <div class="flex gap-3 pt-2">
                            <button type="button" onclick="document.getElementById('downloadForm').action='{{ route('export.pdf') }}'; document.getElementById('downloadForm').submit(); downloadModal = false;" class="flex-1 bg-red-600 hover:bg-red-700 text-white font-bold py-2.5 px-4 rounded-xl text-sm transition-all shadow-sm flex items-center justify-center gap-2 border border-red-700">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                PDF
                            </button>
                            <button type="button" onclick="document.getElementById('downloadForm').action='{{ route('export.excel') }}'; document.getElementById('downloadForm').submit(); downloadModal = false;" class="flex-1 bg-green-600 hover:bg-green-700 text-white font-bold py-2.5 px-4 rounded-xl text-sm transition-all shadow-sm flex items-center justify-center gap-2 border border-green-700">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                Excel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>

    <!-- Script to uncheck 'all' if specific is checked, and vice versa -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const allCheckbox = document.querySelector('input[value="all"]');
            const kkCheckboxes = document.querySelectorAll('.kk-checkbox');

            allCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    kkCheckboxes.forEach(cb => cb.checked = false);
                }
            });

            kkCheckboxes.forEach(cb => {
                cb.addEventListener('change', function() {
                    if (this.checked) {
                        allCheckbox.checked = false;
                    } else {
                        // Check if all are unchecked, then check 'all' again
                        let anyChecked = Array.from(kkCheckboxes).some(c => c.checked);
                        if (!anyChecked) {
                            allCheckbox.checked = true;
                        }
                    }
                });
            });
        });
    </script>
</x-app-layout>
