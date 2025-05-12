<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessCsvUpload;
use App\Models\Upload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class UploadController extends Controller
{
    public function index()
    {
        return view('uploads.index', ['uploads' => Upload::latest()->get()]);
    }

    public function store(Request $request)
    {
        Log::info("request");
        Log::info($request->file('csv_file')->getClientOriginalName());

        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt',
        ]);

        $file = $request->file('csv_file');
        $uploadId = (string) Str::uuid();

        // Simpan log
        $path = $file->store('csv');
        $upload = Upload::create([
            'filename' => $file->getClientOriginalName(),
            'status' => 'pending',
            'uploaded_at' => now(),
        ]);

        ProcessCsvUpload::dispatch($path, $upload->id);

        return redirect()->route('upload')->with('success', 'File uploaded successfully.');
        // return response()->json([
        //     'message' => 'Upload started',
        //     'upload_id' => $uploadId,
        //     'file' => $upload
        // ]);
    }
}
