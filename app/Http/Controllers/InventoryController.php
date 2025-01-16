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
            'products_sold' => 'required|integer',
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
            'products_sold' => 'required|integer',
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

    // GET all products sorted by a specific column and order
    public function sort($column, $order)
    {
        // Ensure the column is valid by allowing only a set of columns
        $validColumns = ['id', 'brand_name', 'type', 'quantity_on_hand', 'price', 'inventory_value', 'products_sold', 'sales_value'];

        if (!in_array($column, $validColumns)) {
            return response()->json(['message' => 'Invalid column name'], 400);
        }

        if (!in_array(strtolower($order), ['asc', 'desc'])) {
            return response()->json(['message' => 'Invalid order value. Must be "asc" or "desc"'], 400);
        }

        $sortedItems = Inventory::orderBy($column, $order)->get();

        return response()->json($sortedItems);
    }

    // GET inventory and sales value report by inventory type
    public function valuationReport($type)
    {
        $inventoryItems = Inventory::where('type', $type);

        // If no items found for the type
        if ($inventoryItems->count() == 0) {
            return response()->json(['error' => 'No inventory found for this type.'], 404);
        }

        $totalQuantity = $inventoryItems->sum('quantity_on_hand');
        $totalInventoryValue = $inventoryItems->sum('inventory_value');
        $totalProductsSold = $inventoryItems->sum('products_sold');
        $totalSalesValue = $inventoryItems->sum('sales_value');

        return response()->json([
            'type' => $type,
            'totalQuantity' => $totalQuantity,
            'totalInventoryValue' => $totalInventoryValue,
            'totalProductsSold' => $totalProductsSold,
            'totalSalesValue' => $totalSalesValue,
        ]);
    }

    // POST import inventory items
    public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:csv,txt|max:2048',  // Corrected line
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $file = $request->file('file');
        $path = $file->getRealPath();

        if (($handle = fopen($path, "r")) !== FALSE) {
            fgetcsv($handle);  // Skip the header row

            $inventoryData = [];

            // Read each row and store the data
            while (($row = fgetcsv($handle)) !== FALSE) {
                $inventoryData[] = [
                    'brand_name' => $row[0],
                    'type' => $row[1],
                    'quantity_on_hand' => (int)$row[2],
                    'price' => (float)$row[3],
                    'products_sold' => (int)$row[4],
                ];
            }

            fclose($handle);

            // Loop through the imported data and validate each row
            foreach ($inventoryData as $item) {
                $validator = Validator::make($item, [
                    'brand_name' => 'required|string|max:255',
                    'type' => 'required|string|max:255',
                    'quantity_on_hand' => 'required|integer|min:0',
                    'price' => 'required|numeric|min:0',
                    'products_sold' => 'required|integer|min:0',
                ]);

                if ($validator->fails()) {
                    return response()->json(['errors' => $validator->errors()], 400);
                }

                // Save the valid item to the database
                Inventory::create($item);
            }

            return response()->json(['success' => 'Inventory imported successfully'], 200);
        }

        return response()->json(['error' => 'Unable to import inventory.'], 500);
    }
}
