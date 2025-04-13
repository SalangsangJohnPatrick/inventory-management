<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Throwable;

class InventoryController extends Controller
{
    // GET all products
    public function index(Request $request)
    {
        try {
            $sortField = $request->input('sortField', 'id');
            $sortOrder = $request->input('sortOrder', 'ASC');
            $search = $request->input('search');
            $currentPage = $request->input('currentPage', 1);
            $itemsPerPage = $request->input('itemsPerPage', 20);
            $filters = $request->input('filterForm');

            $query = Inventory::orderBy($sortField, $sortOrder);

            if ($search) {
                $query->where('brand_name', 'like', '%' . $search . '%')
                    ->orWhere('type', 'like', '%' . $search . '%')
                    ->orWhere('quantity_on_hand', 'like', '%' . $search . '%')
                    ->orWhere('price', 'like', '%' . $search . '%')
                    ->orWhere('products_sold', 'like', '%' . $search . '%')
                    ->orWhere('inventory_value', 'like', '%' . $search . '%')
                    ->orWhere('sales_value', 'like', '%' . $search . '%');
            }

            // Apply filters
            if (!empty($filters) && is_array($filters)) {
                foreach ($filters as $column => $value) {
                    if (!is_null($value) && $value !== '') {
                        $query->where($column, $value);
                    }
                }
            }      

            $inventories = $query->paginate($itemsPerPage, ['*'], 'page', $currentPage);

            return response()->json([
                'data' => $inventories->items(),
                'pagination' => [
                    'total' => $inventories->total(),
                    'per_page' => $inventories->perPage(),
                    'current_page' => $inventories->currentPage(),
                    'last_page' => $inventories->lastPage(),
                    'from' => $inventories->firstItem(),
                    'to' => $inventories->lastItem(),
                ],
            ]);
        } catch (Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getInventoryDropdown()
    {
        $filter = [
            'brand_names' => Inventory::select('brand_name')->distinct()->pluck('brand_name'),
            'types' => Inventory::select('type')->distinct()->pluck('type'),
        ];

        return response()->json($filter);
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
            'data' => [
                'type' => $type,
                'totalQuantity' => $totalQuantity,
                'totalInventoryValue' => $totalInventoryValue,
                'totalProductsSold' => $totalProductsSold,
                'totalSalesValue' => $totalSalesValue,
            ]
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

    // GET top-selling products
    public function getTopSellingProducts()
    {
        $topSellingProducts = Inventory::orderBy('products_sold', 'desc')
            ->take(10) // You can limit the number of top-selling products
            ->get(['brand_name', 'products_sold', 'sales_value']); // Fetch only relevant fields

        if ($topSellingProducts->isEmpty()) {
            return response()->json(['message' => 'No top-selling products found'], 404);
        }

        return response()->json($topSellingProducts);
    }

    // GET low stock items
    public function getLowStockItems()
    {
        $threshold = 100; // You can define the threshold here or pass it as a parameter

        $lowStockItems = Inventory::where('quantity_on_hand', '<', $threshold)
            ->take(10)
            ->get(['brand_name', 'type', 'quantity_on_hand']); // Fetch only relevant fields

        if ($lowStockItems->isEmpty()) {
            return response()->json(['message' => 'No low-stock items found'], 404);
        }

        return response()->json([
            'threshold' => $threshold,
            'data' => $lowStockItems
        ]);
    }
}
