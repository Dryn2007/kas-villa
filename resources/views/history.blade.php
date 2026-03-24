<x-app-layout>
    <div class="max-w-md mx-auto bg-gray-50 min-h-screen pb-12 font-sans pt-6 px-5">

        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                <svg class="w-6 h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Riwayat Lengkap
            </h1>
            <a href="{{ route('dashboard') }}"
                class="text-sm bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2 px-4 rounded-full transition-all flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Kembali
            </a>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-6">
            @forelse($semuaHistory as $history)
                <div
                    class="p-4 border-b border-gray-50 flex items-center justify-between last:border-0 hover:bg-gray-50 transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full overflow-hidden flex-shrink-0 border border-gray-200 shadow-sm">
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
                        <p class="text-[10px] text-gray-400 mt-1">{{ $history->updated_at->format('d M Y, H:i') }}</p>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-gray-500 font-medium">Belum ada riwayat pembayaran sama sekali.</div>
            @endforelse
        </div>

        <div class="mt-4">
            {{ $semuaHistory->links() }}
        </div>

    </div>
</x-app-layout>