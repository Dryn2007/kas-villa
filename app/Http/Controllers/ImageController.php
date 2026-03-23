<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CloudinaryService;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class ImageController extends Controller
{
    protected $cloudinaryService;

    public function __construct(CloudinaryService $cloudinaryService)
    {
        $this->cloudinaryService = $cloudinaryService;
    }

    /**
     * Upload profile image
     */
    public function uploadProfileImage(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120', // Max 5MB
        ]);

        try {
            // Upload ke Cloudinary
            $result = $this->cloudinaryService->uploadProfileImage($request->file('avatar'));

            if (!$result['success']) {
                return back()->with('error', 'Gagal upload gambar: ' . $result['message']);
            }

            // Update user avatar
            $user = Auth::user();
            $user->update(['avatar' => $result['url']]);

            return back()->with('success', 'Foto profile berhasil diperbarui! ✅');
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Upload dokumen/file
     */
    public function uploadFile(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // Max 10MB
        ]);

        try {
            $result = $this->cloudinaryService->upload($request->file('file'), 'kas-villa/documents', 'raw');

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                ], 400);
            }

            return response()->json([
                'success' => true,
                'url' => $result['url'],
                'public_id' => $result['public_id'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
