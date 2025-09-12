<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\cart_items;
use Illuminate\Http\Request;
use PHPUnit\Framework\Constraint\IsEmpty;

class CartController extends Controller
{
    //

    public function addToCart(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|integer',
            'quantity'   => 'required|integer|min:1',
            'price'      => 'required|numeric'
        ]);

        // Authenticated user (via token)
        $user = $request->user();

        // Get or create cart for this user
        $cart = Cart::firstOrCreate([
            'user_id' => $user->id,
        ]);

        // If same product exists in cart, update quantity instead of duplicate
        $item = $cart->items()->where('product_id', $validated['product_id'])->first();

        if ($item) {
            $item->update([
                'quantity' => $item->quantity + $validated['quantity'],
                'price'    => $validated['price'], // overwrite latest price snapshot
            ]);
        } else {
            $item = $cart->items()->create([
                'product_id' => $validated['product_id'],
                'quantity'   => $validated['quantity'],
                'price'      => $validated['price'],
            ]);
        }

        return response()->json([
            'message' => 'Item added to cart',
            'item'    => $item
        ], 201);
    }

     public function viewCart(Request $request)
    {
        $user = $request->user();
        $cart = Cart::with('items.product')->where('user_id', $user->id)->first();

        return response()->json($cart);
    }

    // Remove item
    public function removeItem(Request $request, $id)
    {
        $user = $request->user();
        $item = cart_items::whereHas('cart', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })->findOrFail($id);

        $item->delete();

        return response()->json(['message' => 'Item removed']);
    }
}
