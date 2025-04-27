<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Helpers\Helper;
use Illuminate\Support\Facades\Log;

class FileUploadService
{
    /**
     * Upload a file to storage
     */
    public function uploadFile(UploadedFile $file, $directory = 'uploads', $filename = null)
    {
        Log ::debug('File to be uploaded:', ['file' => $file, 'filename' => $filename]);
        
        if (!$filename) {
            $filename = Helper::generateRandomString(16) . '.' . $file->getClientOriginalExtension();
        }
        
        // Check if the file is valid
    if ($file->isValid()) {
        $path = $file->storeAs($directory, $filename, 'public');

        Log::debug('File uploaded successfully:', ['path' => $path, 'filename' => $filename]);

        return [
            'path' => $path,
            'url' => Storage::url($path),
            'filename' => $filename,
            'directory' => $directory,
        ];
    } else {
        Log::error('File upload failed: Invalid file.');
        return null;
    }
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