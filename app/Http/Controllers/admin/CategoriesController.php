<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class CategoriesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $categories = Category::orderBy('created_at', 'desc')->get();

        return response()->json([
            'status' => 200,
            'data' => $categories,
        ], 200);
    }

    

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

        $category = Category::create($validated->validated());

        return response()->json([
            'status' => 201,
            'message' => 'Category created successfully',
            'data' => $category,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $category = Category::find($id);

        if(!$category){
            return response()->json([
                'status' => 404,
                'message' => 'Category not found',
            ], 404);
        }

        return response()->json([
            'status' => 200,
            'data' => $category,
        ], 200);
    }

    
  

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $category = Category::find($id);

        if(!$category){
            return response()->json([
                'status' => 404,
                'message' => 'Category not found',
            ], 404);
        }

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

        $category->update($validated->validated());

        return response()->json([
            'status' => 200,
            'message' => 'Category updated successfully',
            'data' => $category,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $category = Category::find($id);
        if(!$category){
            return response()->json([
                'status' => 404,
                'message' => 'Category not found',
            ], 404);
        }

        $category->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Category deleted successfully',
        ], 200);

    }

}