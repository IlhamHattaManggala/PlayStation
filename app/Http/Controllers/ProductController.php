<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::orderBy('name')->get();
        return view('products.index', compact('products'));
    }

    public function apiList()
    {
        $products = Product::orderBy('name')->get();
        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:products,name',
            'category' => 'required|in:Makanan,Minuman,Jajanan',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
        ]);

        $product = Product::create($request->all());

        return response()->json([
            'success' => true,
            'message' => "Produk {$product->name} berhasil ditambahkan.",
            'data' => $product
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:products,name,' . $product->id,
            'category' => 'required|in:Makanan,Minuman,Jajanan',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
        ]);

        $product->update($request->all());

        return response()->json([
            'success' => true,
            'message' => "Produk {$product->name} berhasil diperbarui.",
            'data' => $product
        ]);
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return response()->json([
            'success' => true,
            'message' => "Produk berhasil dihapus."
        ]);
    }
}
