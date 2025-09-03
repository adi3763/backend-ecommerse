<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Container\Attributes\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductImagesController extends Controller
{
    //
   public function destroy($productId, $imageId)
    {
        // Always scope by product_id + id to avoid accidental deletes
        $img = ProductImage::where('product_id', $productId)
            ->where('id', $imageId)
            ->first();

        if (!$img) {
            return response()->json(['message' => 'Image not found for this product.'], 404);
        }

        try {
            // --- delete file (non-blocking) ---
            $relativePath = $img->image ?? $img->path ?? null; // support either column
            if ($relativePath && \Illuminate\Support\Facades\Storage::disk('public')->exists($relativePath)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($relativePath);
            }

            // --- delete DB row (make it explicit & check affected rows) ---
            // If you DO NOT use SoftDeletes on ProductImage:
            $affected = ProductImage::where('product_id', $productId)
                ->where('id', $imageId)
                ->delete();  // returns number of rows affected

            // If you DO use SoftDeletes and want a HARD delete, replace the 3 lines above with:
            // $affected = ProductImage::withTrashed()
            //     ->where('product_id', $productId)
            //     ->where('id', $imageId)
            //     ->forceDelete();

            if ($affected < 1) {
                // Nothing was deleted – log and tell client
                Log::warning('Product image delete: no rows affected', [
                    'product_id' => $productId,
                    'image_id'   => $imageId,
                ]);
                return response()->json([
                    'message' => 'No database rows were deleted.',
                ], 409);
            }

            return response()->json([
                'message' => 'Image deleted successfully.',
                'deleted_rows' => $affected,
            ]);
        } catch (\Throwable $e) {
            Log::error('Product image delete failed', [
                'product_id' => $productId,
                'image_id'   => $imageId,
                'error'      => $e->getMessage(),
            ]);
            return response()->json([
                'message' => 'Failed to delete image.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

     public function makePrimary(Product $product, $imageId)
    {
        // Re-fetch the image scoped to the product to guarantee it exists & belongs
        $img = ProductImage::where('product_id', $product->id)
            ->where('id', $imageId)
            ->first();

           

        if (!$img) {
            return response()->json([
                'message' => 'Image not found for this product.'
            ], 404);
        }

        try {
            DB::transaction(function () use ($product, $img) {
                // 1) Clear previous primaries for this product
                ProductImage::where('product_id', $product->id)
                    ->where('is_primary', 1)
                    ->update(['is_primary' => 0]);

                // 2) Mark selected image as primary (direct UPDATE by id)
                ProductImage::where('id', $img->id)
                    ->update(['is_primary' => 1]);

                // 3) (Optional) cache on product for quick reads
                // if you keep these columns:
                // $product->primary_image_id = $img->id;
                // $product->primary_image_path = $img->image; // or 'path', see note below
                // $product->save();
            });

            return response()->json([
                'message' => 'Primary image updated successfully.',
                'image_id' => $img->id,
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Failed to update primary image.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

}
