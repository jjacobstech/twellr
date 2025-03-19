<?php

namespace App\Helpers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Nette\Utils\Helpers;

class ProductHelper extends Helpers
{
    public static function SaveToDb(Request $request, $fileData, $path)
    {

        $receiver = $request->receiver;
        $title = $request->title;
        $description = $request->description;
        $subject = $request->subject;
        $assignedTo = $request->assigned_to;


        $saveFile = Product::create([
            'original_file_name' => $fileData->originalFileName,
            'file_name' => $fileData->name,
            'file_size' => $fileData->size,
            'file_path' => $path,
            'file_type' => $fileData->extension,
            'title' => $title,
            'description' => $description,
            'sender_id' => Auth::id(),
            'receiver_id' => $receiver,
            'subject' => $subject,
            'dept_in_request' => Auth::user()->department,
            'assigned_to' => $assignedTo,

        ]);

        if (!$saveFile) {
            return response()->json(['messsage' => 'server error'], 500);
        }

        return true;
    }

    public static function getProducts($fileType)
    {

        if ($fileType == 'shared_files') {
            $column = 'sender_id';
        } elseif ($fileType == 'received_files') {
            $column = 'receiver_id';
        }

        $Files = Product::where($column, '=', Auth::id())->get();

        if (!$Files) {
            return response()->json(['status' => false, 'error' => 'server error'], 500);
        }

        return response()->json(['status' => true, 'data' => $Files], 200);
    }

    public static function modifyProductStatus(String $id, $status)
    {

        $file = Product::where('id', '=', $id)->first();

        if (!$file) {
            return response()->json(['status' => false, 'error' => 'file does not exist'], 400);
        }

        $file->status = $status;

        $saved = $file->save();

        if (!$saved) {
            return response()->json(['status' => false, 'error' => 'server error'], 500);
        }

        return response()->json(['status' => true, 'messager' => 'file ' . $status], 200);
    }
}
