<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Helpers\Helper;

class FileUploadService
{
    /**
     * Upload a file to storage
     */
    public function uploadFile(UploadedFile $file, $directory = 'uploads', $filename = null)
    {
        if (!$filename) {
            $filename = Helper::generateRandomString(16) . '.' . $file->getClientOriginalExtension();
        }
        
        $path = $file->storeAs($directory, $filename, 'public');
        
        return [
            'path' => $path,
            'url' => Storage::url($path),
            'filename' => $filename,
            'directory' => $directory,
        ];
    }

    /**
     * Delete a file from storage
     */
    public function deleteFile($path)
    {
        if (Storage::exists($path)) {
            return Storage::delete($path);
        }
        
        return false;
    }
}