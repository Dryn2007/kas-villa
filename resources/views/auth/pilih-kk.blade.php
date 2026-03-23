<x-guest-layout>
    <div class="max-w-md mx-auto p-4 font-sans">
        <div class="text-center mb-6 mt-4">
            <div class="text-5xl mb-3">👋</div>
            <h2 class="text-2xl font-extrabold text-gray-800">Satu Langkah Lagi!</h2>
            <p class="text-sm text-gray-500 mt-2 leading-relaxed">Pilih namamu / keluarga yang kamu wakili. Pilihan yang
                tergembok berarti sudah login.</p>
        </div>

        @if(session('error'))
            <div class="bg-red-50 border-l-4 border-red-500 text-red-600 p-3 rounded-r text-sm mb-4 font-bold shadow-sm">
                {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('claim-kk') }}">
            @csrf

            <div class="space-y-3 max-h-[60vh] overflow-y-auto px-1 py-2" style="scrollbar-width: thin;">
                @foreach($semuaKk as $kk)

                    @if($kk->google_id)
                        <div
                            class="p-4 rounded-2xl border-2 bg-gray-50 border-gray-200 opacity-60 flex justify-between items-center cursor-not-allowed">
                            <span class="font-bold text-gray-500">Keluarga {{ $kk->name }}</span>
                            <span class="text-xs font-bold text-gray-500 bg-gray-200 px-2 py-1 rounded">Terisi 🔒</span>
                        </div>
                    @else
                        <button type="submit" name="kk_id" value="{{ $kk->id }}"
                            class="w-full text-left p-4 rounded-2xl border-2 bg-white border-gray-200 hover:border-teal-500 hover:bg-teal-50 shadow-sm hover:shadow-md active:scale-95 transition-all flex justify-between items-center group cursor-pointer">
                            <span class="font-bold text-gray-800 group-hover:text-teal-700">Keluarga {{ $kk->name }}</span>
                            <span
                                class="text-xs font-bold text-teal-600 bg-teal-50 border border-teal-100 px-3 py-1 rounded-full group-hover:bg-teal-600 group-hover:text-white transition-colors shadow-sm">
                                Pilih & Masuk 👉
                            </span>
                        </button>
                    @endif

                @endforeach
            </div>
        </form>
    </div>
</x-guest-layout>