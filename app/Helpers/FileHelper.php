<?php

namespace App\Helpers;

use App\Models\Files;
use App\Models\Product;
use File;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\FileUploadController;
use Symfony\Component\Console\Helper\HelperInterface;

class FileHelper
{
    public static function getFileData(UploadedFile $file)
    {

        $originalFileName = $file->getClientOriginalName();
        $fileExtension = $file->getClientOriginalExtension();
        $fileMime = $file->getMimeType();
        $fileSize = $file->getSize();
        $fileName = time() . strtolower(Auth::user()->firstname . Auth::user()->lastname) . (25) . Str::random(25) . '.' . $fileExtension;

        return (object) ['name' => $fileName, 'originalFileName' => $originalFileName, 'extension' => $fileExtension, 'mime' => $fileMime, 'size' => $fileSize];
    }
    public static function saveFile(UploadedFile $file, $filename)
    {

        $storage = Storage::disk()->putFileAs('', $file, $filename);

        if (!$storage) {
            return response()->json(['messsage' => 'server error'], 500);
        }
        if (Storage::exists($storage)) {
            return response()->json(['messsage' => 'file exists'], 403);
        }

        return (object) ['path' => $storage];
    }
}
