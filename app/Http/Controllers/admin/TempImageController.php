<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\TempImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Imagick\Driver;

class TempImageController extends Controller
{
    public function store(Request $request)
    {
        // NOTE: ensure your file input is "name". If it's "image", this still works.
        $request->validate([
            'name'  => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        // accept either field name
        $file = $request->file('name') ?? $request->file('image');
        if (!$file) {
            return response()->json(['status' => 422, 'message' => 'No image file received.'], 422);
        }

        $filename = Str::uuid()->toString().'.'.$file->extension();

        // 1) Save original
        $originalPath = $file->storeAs('tmpImages', $filename, 'public'); // storage/app/public/tmpImages/...
        
        // 2) Create & save thumbnail
        $thumbDir  = 'tmpImages/thumbnail';
        $thumbPath = $thumbDir.'/'.$filename;

        // make sure the directory exists
        Storage::disk('public')->makeDirectory($thumbDir);

        // Intervention Image v3
$manager = new ImageManager(new Driver());
        $img = $manager->read($file->getRealPath());     // read uploaded temp file
        $img->scaleDown(400, 450);                       // fit within 400x450, keep aspect
        Storage::disk('public')->put($thumbPath, (string) $img->encode());

        // 3) DB row
        $temp = TempImage::create([
            'name' => $filename, // or store $originalPath if you prefer
        ]);

        return response()->json([
            'status' => 201,
            'data' => [
                'id'        => $temp->id,
                'name'      => $temp->name,
                'url'       => Storage::disk('public')->url($originalPath),
                'thumbnail' => Storage::disk('public')->url($thumbPath),
            ],
        ], 201);
    }
}
