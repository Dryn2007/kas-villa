<x-app-layout>
    <div class="max-w-md mx-auto bg-gray-50 min-h-screen pb-12 font-sans">

        <div class="bg-teal-600 text-white p-6 rounded-b-3xl shadow-lg relative overflow-hidden">
            <div class="absolute top-0 right-0 opacity-10 text-9xl -mt-4 -mr-4">🌴</div>
            <div class="relative z-10">
                <p class="text-teal-100 text-sm font-medium">Selamat datang, {{ Auth::user()->name }} 👋</p>
                <h1 class="text-2xl font-bold mb-4">Kas Villa Keluarga</h1>

                <div class="bg-white/20 backdrop-blur-sm rounded-2xl p-4 border border-white/30">
                    <p class="text-sm text-teal-50 mb-1">Total Terkumpul</p>
                    <div class="flex justify-between items-end mb-2">
                        <h2 class="text-2xl font-extrabold">Rp {{ number_format($totalTerkumpul, 0, ',', '.') }}</h2>
                        <span class="text-sm font-medium">/ Rp 10.985k</span>
                    </div>
                    <div class="w-full bg-teal-800 rounded-full h-3">
                        <div class="bg-yellow-400 h-3 rounded-full" style="width: {{ $persentase }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        @if(request()->has('kk_id'))
            <div class="px-5 mt-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-800 flex items-center">
                        👨‍👩‍👦 Iuran {{ $selectedKk->name }}
                    </h3>
                    <a href="{{ route('dashboard') }}"
                        class="text-sm bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-1 px-3 rounded-full transition-all">
                        ⬅ Kembali
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

                @php
                    $payableTagihans = $tagihanKk->whereNotIn('status', ['lunas', 'proses'])->values();
                    $payableIds = $payableTagihans->pluck('id');
                @endphp

                <form id="bulkPaymentForm" action="{{ route('dummy.pay.bulk') }}" method="POST" x-data="{ 
                    selected: [],
                    payableIds: {{ $payableIds->toJson() }},
                    metode: 'online',
                    
                    submitForm(pilihan) {
                        this.metode = pilihan;
                        // Tunggu x-model / DOM update, lalu submit form
                        $nextTick(() => { document.getElementById('bulkPaymentForm').submit(); });
                    },
                    
                    toggle(id) {
                        let idStr = id.toString();
                        let idx = this.payableIds.indexOf(id);

                        if (!this.selected.includes(idStr)) {
                            for(let i = 0; i <= idx; i++) {
                                let curr = this.payableIds[i].toString();
                                if(!this.selected.includes(curr)) this.selected.push(curr);
                            }
                        } else {
                            for(let i = idx; i < this.payableIds.length; i++) {
                                let curr = this.payableIds[i].toString();
                                let pos = this.selected.indexOf(curr);
                                if(pos !== -1) this.selected.splice(pos, 1);
                            }
                        }
                    },
                    
                    isAllowed(id) {
                        let idx = this.payableIds.indexOf(id);
                        if (idx === 0) return true; 
                        let prevId = this.payableIds[idx - 1].toString();
                        return this.selected.includes(prevId);
                    }
                }" class="pb-28"> @csrf
                    <input type="hidden" name="metode" :value="metode">
                    
                    @foreach ($tagihanKk as $tagihan)
                        @if ($tagihan->status === 'lunas')
                            <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100 flex justify-between mb-3 opacity-60">
                                <div class="flex items-center gap-4">
                                    <div class="bg-green-100 p-2 rounded-full text-green-600 text-lg">✅</div>
                                    <div>
                                        <p class="font-bold text-gray-800">Bulan {{ $tagihan->bulan_ke }}</p>
                                        <p class="text-xs text-gray-500">Rp {{ number_format($tagihan->nominal, 0, ',', '.') }}</p>
                                    </div>
                                </div>
                                <span class="bg-green-100 text-green-700 py-1 px-3 rounded-full text-xs font-bold border border-green-200 mt-1 h-max">Lunas</span>
                            </div>

                        @elseif ($tagihan->status === 'proses')
                            <div class="bg-white rounded-2xl p-4 shadow-sm border border-yellow-200 flex justify-between mb-3 bg-gradient-to-r from-yellow-50 to-white">
                                <div class="flex items-center gap-4">
                                    <div class="bg-yellow-100 p-2 rounded-full text-yellow-600 text-lg animate-pulse">⏳</div>
                                    <div>
                                        <p class="font-bold text-gray-800">Bulan {{ $tagihan->bulan_ke }}</p>
                                        <p class="text-xs text-gray-500">Rp {{ number_format($tagihan->nominal, 0, ',', '.') }}</p>
                                    </div>
                                </div>
                                <span class="bg-yellow-100 text-yellow-700 py-1 px-3 rounded-full text-xs font-bold border border-yellow-200 mt-1 h-max shadow-sm">Menunggu ACC</span>
                            </div>

                        @else
                            <label class="bg-white rounded-2xl p-4 shadow-sm border-l-4 border-l-red-500 flex justify-between items-center mb-3 transition-all cursor-pointer relative"
                                 :class="!isAllowed({{ $tagihan->id }}) ? 'opacity-60 bg-gray-50' : 'hover:bg-red-50'">
                                 
                                <div x-show="!isAllowed({{ $tagihan->id }})" class="absolute inset-0 z-10 cursor-not-allowed"></div>

                                <div class="flex items-center gap-4">
                                    <input type="checkbox" name="tagihan_ids[]" value="{{ $tagihan->id }}" 
                                        :checked="selected.includes('{{ $tagihan->id }}')"
                                        @click.prevent="toggle({{ $tagihan->id }})"
                                        :disabled="!isAllowed({{ $tagihan->id }})"
                                        class="w-6 h-6 text-teal-600 border-gray-300 rounded focus:ring-teal-500 disabled:bg-gray-200 transition-colors cursor-pointer relative z-20">
                                    <div>
                                        <p class="font-bold text-gray-800 text-lg">Bulan {{ $tagihan->bulan_ke }}</p>
                                        <p class="text-sm text-gray-500">Rp {{ number_format($tagihan->nominal, 0, ',', '.') }}</p>
                                    </div>
                                </div>
                                
                                <div x-show="!isAllowed({{ $tagihan->id }})" class="text-[10px] text-gray-500 font-bold bg-gray-200 px-2 py-1 rounded">
                                    🔒 Kunci
                                </div>
                            </label>
                        @endif
                    @endforeach

                    <div x-show="selected.length > 0" x-transition.duration.300ms class="fixed bottom-6 left-1/2 transform -translate-x-1/2 w-full max-w-sm px-5 z-50">
                        <div class="bg-white p-3 rounded-3xl shadow-2xl border-2 border-teal-100 flex flex-col gap-3 backdrop-blur-md bg-white/90">
                            <p class="text-center text-sm font-extrabold text-gray-700">Pilih Metode Pembayaran (<span x-text="selected.length"></span> bln):</p>
                            
                            <div class="flex gap-2">
                                <button type="button" @click="submitForm('tunai')" class="w-1/2 bg-yellow-400 hover:bg-yellow-500 text-yellow-900 font-extrabold py-3 px-2 rounded-2xl shadow-sm flex flex-col justify-center items-center transition-all active:scale-95">
                                    <span class="text-xl mb-1">💵</span>
                                    <span class="text-[11px] uppercase tracking-wide">Titip Admin</span>
                                </button>
                                
                                <button type="button" @click="submitForm('online')" class="w-1/2 bg-teal-600 hover:bg-teal-700 text-white font-extrabold py-3 px-2 rounded-2xl shadow-sm flex flex-col justify-center items-center transition-all active:scale-95 border border-teal-500">
                                    <span class="text-xl mb-1">💳</span>
                                    <span class="text-[11px] uppercase tracking-wide">Online (Duitku)</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

        @else
            <div x-data="{ openModal: false }" class="px-5 mt-6">
                <h3 class="text-lg font-bold text-gray-800 mb-3">Pilih Tagihan 💸</h3>

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
                                    <p class="text-xs text-gray-500">Membayar Bulan ke-{{ $history->bulan_ke }}</p>
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
    </div>
</x-app-layout>