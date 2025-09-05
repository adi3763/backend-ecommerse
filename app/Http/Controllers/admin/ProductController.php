<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use GuzzleHttp\Psr7\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Eager-load images to avoid N+1
        $products = Product::with(['images', 'primaryImage'])->latest('id')->get();

        if ($products->isEmpty()) {
            return response()->json([
                'status' => 404,
                'message' => 'No products found'
            ], 404);
        }

        return response()->json([
            'status'   => 200,
            'message'  => 'Products found',
            'products' => $products
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // VALIDATION
        $validated = Validator::make($request->all(), [
            // DO NOT require 'id' or 'product_id' on create
            'title'        => 'required|string|max:255',
            'category_id'  => 'required|integer',      // use exists:categories,id if you have categories
            'brand_id'     => 'required|integer',      // use exists:brands,id if you have brands
            'qty'          => 'required|integer|min:0',
            'sku'          => 'required|string|max:255|unique:products,sku',
            'barcode'      => 'required|string|max:255',
            'price'        => 'required|numeric|min:0',
            'status'       => 'required|in:0,1,active,inactive', // adjust to your enum
            'is_featured'  => 'required|in:0,1',
            // images are OPTIONAL; if present they must pass rules
            'images'       => 'nullable|array',
            'images.*'     => 'image|mimes:jpg,jpeg,png,webp,gif|max:5120',
            'primary_index'=> 'nullable|integer|min:0',
        ]);

        if ($validated->fails()) {
            return response()->json([
                'status' => 422,
                'errors' => $validated->errors(),
            ], 422);
        }

        // CREATE + ATTACH IMAGES (transaction-safe)
        try {
            DB::beginTransaction();

            // Create product with only fillable keys (avoid mass-assigning unexpected stuff)
            $product = Product::create([
                'title'       => $request->title,
                'category_id' => $request->category_id,
                'brand_id'    => $request->brand_id,
                'qty'         => $request->qty,
                'sku'         => $request->sku,
                'barcode'     => $request->barcode,
                'status'      => $request->status,
                'is_featured' => $request->is_featured,
                'price'       => $request->price,
            ]);

            // Save images if provided
            if ($request->hasFile('images')) {
                $primaryIndex = (int) $request->input('primary_index', 0);

                foreach ($request->file('images') as $idx => $file) {
                    $path = $file->store('products', 'public'); // storage/app/public/products/...

                    $product->images()->create([
                        'image'      => $path,
                        'is_primary' => $primaryIndex === $idx,
                        // 'sort_order' => $idx,
                    ]);
                }

                // Ensure exactly one primary (fallback to first)
                if (!$product->images()->where('is_primary', true)->exists()) {
                    $first = $product->images()->orderBy('id')->first();
                    if ($first) {
                        $first->update(['is_primary' => true]);
                    }
                }
            }

            DB::commit();

            // Return with images eager-loaded
            $product->load(['images', 'primaryImage']);

            return response()->json([
                'status'  => 201,
                'message' => 'Product created successfully',
                'data'    => $product,
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status'  => 500,
                'message' => 'Product not created',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $product = Product::with(['images', 'primaryImage'])->find($id);

        if (!$product) {
            return response()->json([
                'status'  => 404,
                'message' => 'Product not found'
            ], 404);
        }

        return response()->json([
            'status'  => 200,
            'message' => 'Product found',
            'data'    => $product,
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'status'  => 404,
                'message' => 'Product not found'
            ], 404);
        }

        $validated = Validator::make($request->all(), [
            'title'        => 'sometimes|string|max:255',
            'category_id'  => 'sometimes|integer',
            'brand_id'     => 'sometimes|integer',
            'qty'          => 'sometimes|integer|min:0',
            'sku'          => [
                'sometimes','string','max:255',
                Rule::unique('products','sku')->ignore($product->id)
            ],
            'barcode'      => 'sometimes|string|max:255',
            'status'       => 'sometimes',
            'is_featured'  => 'sometimes',
            // Optional images on update (if you support adding more here)
            'images'       => 'sometimes|array',
            'images.*'     => 'image|mimes:jpg,jpeg,png,webp,gif|max:5120',
            'primary_index'=> 'nullable|integer|min:0',
        ]);

        if ($validated->fails()) {
            return response()->json([
                'status' => 422,
                'errors' => $validated->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            $product->update([
                'title'       => $request->title,
                'category_id' => $request->category_id,
                'brand_id'    => $request->brand_id,
                'qty'         => $request->qty,
                'sku'         => $request->sku,
                'barcode'     => $request->barcode,
                'status'      => $request->status,
                'is_featured' => $request->is_featured,
                'price'       => $request->price,
            ]);

            // If you want to allow adding more images on update:
            if ($request->hasFile('images')) {
$startIdx = $product->images()->count(); // or just ignore startIdx entirely
                $primaryIndex = (int) $request->input('primary_index', -1);

                foreach ($request->file('images') as $offset => $file) {
                    $path = $file->store('products', 'public');
                    // $idx  = $startIdx + $offset;

                    $product->images()->update([
                        'image'      => $path,
                        'is_primary' => false, // don’t override existing primary unless asked explicitly
                        // 'sort_order' => $idx,
                    ]);
                }

                // if primary_index is provided specifically for the new batch, you can toggle here
                if ($primaryIndex >= 0) {
                    $product->images()->update(['is_primary' => false]);
                    $newPrimary = $product->images()->orderBy('id')->skip($primaryIndex)->first();
                    if ($newPrimary) {
                        $newPrimary->update(['is_primary' => true]);
                    }
                }
            }

            DB::commit();

            $product->load(['images', 'primaryImage']);

            return response()->json([
                'status'  => 200,
                'message' => 'Product updated successfully',
                'data'    => $product,
            ], 200);

        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status'  => 500,
                'message' => 'Update failed',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'status'  => 404,
                'message' => 'Product not found'
            ], 404);
        }

        // DB FK `cascadeOnDelete()` will delete product_images rows.
        $product->delete();

        return response()->json([
            'status'  => 200,
            'message' => 'Product deleted successfully'
        ], 200);
    }

    public function countProducts()
    {
        $count = DB::table('products')->count();

        if ($count === 0) {
            return response()->json([
                'status'  => 404,
                'message' => 'No products found',
                'count'   => 0,
            ], 404);
        }

        return response()->json([
            'status'  => 200,
            'message' => 'Product count retrieved successfully',
            'count'   => $count,
        ], 200);
    }

    public function newArrivals(){
        $latestPosts = Product::with(['images', 'primaryImage'])->latest('id')->take(10)->get();
        if(!$latestPosts->isNotEmpty()){
            return response()->json([
                'status'  => 404,
                'message' => 'No products found',
            ], 404);
        }

        return response()->json([
            'status'  => 200,
            'message' => 'New arrivals retrieved successfully',
            'data'    => $latestPosts,
        ], 200);
    }

    public function featuredProducts(){
        $featuredProducts = Product::with(['images', 'primaryImage'])->where('is_featured', 1)->get();
        if(!$featuredProducts->isNotEmpty()){
            return response()->json([
                'status'  => 404,
                'message' => 'No featured products found',
            ], 404);
        }

        return response()->json([
            'status'  => 200,
            'message' => 'Featured products retrieved successfully',
            'data'    => $featuredProducts,
        ], 200);
    }
}
