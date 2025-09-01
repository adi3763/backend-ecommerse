<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Size;
use Illuminate\Http\Request;

class SizeController extends Controller
{
    //
    public function show(){
        $sizes = Size::all();

        if(!$sizes){
            return response()->json(['message' => 'No sizes found'], 404);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Size Found',
            'data' => $sizes,
        ], 200);
    }
}
