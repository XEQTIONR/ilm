<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Upload;
use App\Http\Resources\UploadResource;
use Illuminate\Http\Response;

class UploadController extends Controller
{
    //

    public function store(Request $request): UploadResource
    {
        $request->validate([
            'file' => 'required|file|mimes:mp4,mov,avi,wmv,flv,mpeg,mpg,m4v,3gp,3g2,mj2,jpeg,png,jpg,gif,svg', //|max:2048',
        ]);

        $file = $request->file('file');
        $path = $file->store('uploads', 'public');

        $upload = Upload::create([
            'path' => $path,
            'expires_at' => now()->addDays(30),
        ]);

        $upload->refresh();

        return new UploadResource($upload);
    }

    public function destroy(Upload $upload): Response
    {
        $upload->delete();

        return response()->noContent();
    }
}
