<?php

namespace App\Services;

use Cloudinary\Cloudinary;
use Cloudinary\Api\Upload\UploadApi;
use Illuminate\Http\UploadedFile;

class CloudinaryService
{
    protected $cloudinary;
    protected $uploadApi;

    public function __construct()
    {
        $this->cloudinary = new Cloudinary([
            'cloud' => [
                'cloud_name' => config('cloudinary.cloud_name'),
                'api_key' => config('cloudinary.api_key'),
                'api_secret' => config('cloudinary.api_secret'),
            ]
        ]);

        $this->uploadApi = new UploadApi($this->cloudinary);
    }

    /**
     * Upload file ke Cloudinary
     * 
     * @param UploadedFile $file
     * @param string $folder
     * @param string $resource_type
     * @return array
     */
    public function upload(UploadedFile $file, string $folder = 'kas-villa', string $resource_type = 'auto')
    {
        try {
            $result = $this->uploadApi->upload($file->getRealPath(), [
                'folder' => $folder,
                'resource_type' => $resource_type,
                'quality' => 'auto',
                'fetch_format' => 'auto',
            ]);

            return [
                'success' => true,
                'url' => $result['secure_url'],
                'public_id' => $result['public_id'],
                'data' => $result,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Delete file dari Cloudinary
     * 
     * @param string $public_id
     * @return bool
     */
    public function delete(string $public_id): bool
    {
        try {
            $this->uploadApi->destroy($public_id);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Upload dengan optimasi untuk profile picture
     * 
     * @param UploadedFile $file
     * @return array
     */
    public function uploadProfileImage(UploadedFile $file)
    {
        try {
            $result = $this->uploadApi->upload($file->getRealPath(), [
                'folder' => 'kas-villa/profiles',
                'resource_type' => 'image',
                'quality' => 'auto',
                'fetch_format' => 'auto',
                'width' => 200,
                'height' => 200,
                'crop' => 'fill',
                'gravity' => 'face',
            ]);

            return [
                'success' => true,
                'url' => $result['secure_url'],
                'public_id' => $result['public_id'],
                'data' => $result,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get URL dengan transformasi
     * 
     * @param string $public_id
     * @param array $options
     * @return string
     */
    public function getUrl(string $public_id, array $options = []): string
    {
        return $this->cloudinary->image($public_id)->toUrl();
    }
}
