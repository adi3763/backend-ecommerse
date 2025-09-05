<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BrandsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $brands = Brand::orderByDesc('created_at')->get();

        return response()->json([
            'status' => 200,
            'data' => $brands,
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
   
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $validated = Validator::make($request->all(),[
            'name' => 'required|string|max:255',
            'status' => 'required|integer|in:0,1',
        ]);
        if($validated->fails()){
            return response()->json([
                'status' => 422,
                'errors' => $validated->errors(),
            ], 422);
        }

        $brand = Brand::create($validated->validated());

        return response()->json([
            'status' => 201,
            'message' => 'Brand created successfully',
            'data' => $brand,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $brand = Brand::find($id);
        if (!$brand) {
            return response()->json([
                'status' => 404,
                'message' => 'Brand not found',
            ], 404);
        }

        return response()->json([
            'status' => 200,
            'data' => $brand,
        ], 200);
    }

 


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $brand = Brand::find($id);
        if (!$brand) {
            return response()->json([
                'status' => 404,
                'message' => 'Brand not found',
            ], 404);
        }

        $validated = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'status' => 'required|integer|in:0,1',
        ]);
        if ($validated->fails()) {
            return response()->json([
                'status' => 422,
                'errors' => $validated->errors(),
            ], 422);
        }

        $brand->update($validated->validated());

        return response()->json([
            'status' => 200,
            'message' => 'Brand updated successfully',
            'data' => $brand,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $brand = Brand::find($id);
        if(!$brand){
            return response()->json([
                'status' => 404,
                'message' => 'Brand not found',
            ], 404);
        }

        $brand->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Brand deleted successfully',
        ], 200);
    }
}
