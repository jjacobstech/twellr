<?php

namespace App\Helpers;

use Illuminate\Support\Str;
use Spatie\ImageOptimizer\Image;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Spatie\LaravelImageOptimizer\Facades\ImageOptimizer;

class FileHelper
{

    public static function optimizeImage($file){
         ImageOptimizer::optimize($file->getRealPath());
    }

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

        $storage = Storage::disk()->putFileAs('', $file->getRealPath(), $filename);

        if (!$storage) {
            return response()->json(['messsage' => 'server error'], 500);
        }
        if (Storage::exists($storage)) {
            return response()->json(['messsage' => 'file exists'], 403);
        }

        return (object) ['path' => $storage];
    }
}
