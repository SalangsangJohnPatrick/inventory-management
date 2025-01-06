<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InventoryController extends Controller
{
    // GET all products
    public function index()
    {
        return response()->json(Inventory::all());
    }

    // GET a specific product
    public function show($id)
    {
        $product = Inventory::find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        return response()->json($product);
    }

    // POST a new product
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'brand_name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'quantity_on_hand' => 'required|integer',
            'price' => 'required|numeric',
            'products_sold'=> 'required|integer',
        ]);

        // If validation fails, return errors
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $product = Inventory::create($validator->validated());

        return response()->json($product, 201);
    }

    // PUT (update) an existing product
    public function update(Request $request, $id)
    {
        $product = Inventory::find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'brand_name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'quantity_on_hand' => 'required|integer',
            'price' => 'required|numeric',
            'products_sold'=> 'required|integer',
        ]);

        $product->update($validator->validated());

        return response()->json($product);
    }

    // DELETE a product
    public function destroy($id)
    {
        $product = Inventory::find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $product->delete();

        return response()->json(['message' => 'Product deleted successfully']);
    }
}
