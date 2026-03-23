<div class="max-w-xl">
    <section class="space-y-6">
        <header>
            <h2 class="text-lg font-medium text-gray-900">
                {{ __('Update Profile Picture') }}
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                {{ __('Upload foto profile Anda dari Cloudinary.') }}
            </p>
        </header>

        <form method="post" action="{{ route('upload.profile.image') }}" class="mt-6 space-y-6"
            enctype="multipart/form-data" id="uploadProfileForm">
            @csrf

            <div class="flex flex-col gap-4">
                <!-- Preview Avatar -->
                <div class="flex flex-col items-center">
                    <div class="w-24 h-24 rounded-full overflow-hidden border-4 border-teal-200 shadow-lg mb-4">
                        <img id="avatarPreview"
                            src="{{ Auth::user()->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->name) . '&color=0D9488&background=F0FDFA' }}"
                            alt="Profile" class="w-full h-full object-cover">
                    </div>
                    <p class="text-xs text-gray-500 font-medium">{{ Auth::user()->name }}</p>
                </div>

                <!-- File Input -->
                <div>
                    <label for="avatar" class="block text-sm font-medium text-gray-700 mb-2">Pilih Foto</label>
                    <input type="file" id="avatar" name="avatar" accept="image/*"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all"
                        required>
                    <p class="mt-1 text-xs text-gray-500">JPG, PNG, GIF (Max 5MB)</p>
                    @error('avatar')
                        <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Submit Button -->
                <button type="submit"
                    class="w-full bg-teal-600 hover:bg-teal-700 text-white font-bold py-2 px-4 rounded-lg transition-all active:scale-95">
                    Upload Foto Profile ☁️
                </button>
            </div>
        </form>

        <!-- Success Message -->
        @if (session('success'))
            <div class="p-4 bg-green-50 border border-green-200 rounded-lg text-sm font-medium text-green-700">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="p-4 bg-red-50 border border-red-200 rounded-lg text-sm font-medium text-red-700">
                {{ session('error') }}
            </div>
        @endif
    </section>
</div>

<script>
    // Preview image sebelum upload
    document.getElementById('avatar').addEventListener('change', function (e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (event) {
                document.getElementById('avatarPreview').src = event.target.result;
            };
            reader.readAsDataURL(file);
        }
    });
</script>