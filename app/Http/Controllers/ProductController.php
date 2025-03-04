<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function uploadPrintStack(Request $request)
    {
        if ($request->hasFile('file')) {
            $file = $request->file('file');

            // Validate file
            $validator = validator()->make(
                ['file' => $file],
                ['file' => 'max:5120|mimes:jpg,jpeg,png,gif,pdf']
            );

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 400);
            }

            // Generate unique filename
            $filename = time() . '_' . $file->getClientOriginalName();

            // Store file in public disk under uploads folder
            $path = $file->storeAs('uploads', $filename, 'public');

            return response()->json([
                'success' => true,
                'file' => $filename,
                'path' => Storage::url($path)
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No file uploaded'
        ], 400);
    }
}