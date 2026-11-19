<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('products');

        if ($request->has('category')) {
            $query->where('category', $request->query('category'));
        }

        return response()->json([
            'success' => true,
            'data' => $query->get()
        ], 200);
    }

    public function show(string $id)
    {
        $product = DB::table('products')->where('id', $id)->first();

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Resource Produk tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $product
        ], 200);
    }

    public function store(Request $request)
    {
        $clientApp = $request->header('X-Client-App');

        if (!$request->has('name') || !$request->has('price')) {
            return response()->json([
                'success' => false,
                'message' => 'Bad Request: Field name dan price wajib diisi'
            ], 400);
        }

        $now = now();
        $newId = DB::table('products')->insertGetId([
            'name' => $request->input('name'),
            'category' => $request->input('category', 'uncategorized'),
            'price' => $request->input('price'),
            'stock' => $request->input('stock', 0),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $newProduct = (array) DB::table('products')->where('id', $newId)->first();
        $newProduct['client_source'] = $clientApp ?? 'Unknown';

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil dibuat',
            'data' => $newProduct
        ], 201);
    }

    // PUT: Full Replacement (field yang tidak dikirim diisi null)
    public function updatePut(Request $request, string $id)
    {
        $existing = DB::table('products')->where('id', $id)->first();

        if (!$existing) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        DB::table('products')
            ->where('id', $id)
            ->update([
                'name' => $request->input('name', null),
                'category' => $request->input('category', null),
                'price' => $request->input('price', null),
                'stock' => $request->input('stock', null),
                'updated_at' => now(),
            ]);

        $updatedData = DB::table('products')->where('id', $id)->first();

        return response()->json([
            'method' => 'PUT (Full Replace)',
            'data' => $updatedData
        ], 200);
    }

    // PATCH: Partial Update (hanya memperbarui field yang dikirimkan)
    public function updatePatch(Request $request, string $id)
    {
        $existing = DB::table('products')->where('id', $id)->first();

        if (!$existing) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        $updateData = [];
        if ($request->has('name')) {
            $updateData['name'] = $request->input('name');
        }
        if ($request->has('category')) {
            $updateData['category'] = $request->input('category');
        }
        if ($request->has('price')) {
            $updateData['price'] = $request->input('price');
        }
        if ($request->has('stock')) {
            $updateData['stock'] = $request->input('stock');
        }

        if (!empty($updateData)) {
            $updateData['updated_at'] = now();
            DB::table('products')->where('id', $id)->update($updateData);
        }

        $updatedData = DB::table('products')->where('id', $id)->first();

        return response()->json([
            'method' => 'PATCH (Partial Update)',
            'data' => $updatedData
        ], 200);
    }

    // DELETE: Menghapus data di database & mengembalikan 204 No Content
    public function destroy(string $id)
    {
        $existing = DB::table('products')->where('id', $id)->first();

        if (!$existing) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        DB::table('products')->where('id', $id)->delete();

        return response()->noContent();
    }

    public function applyDiscount(Request $request)
    {
        $authHeader = $request->header('Authorization');

        if (!$authHeader) {
            return response()->json(['message' => 'Token autentikasi tidak ditemukan'], 401);
        }

        if ($authHeader === 'Bearer user-token') {
            return response()->json(['message' => 'Akses ditolak: Hanya Admin yang boleh memberikan diskon'], 403);
        }

        if ($authHeader === 'Bearer admin-token') {
            return response()->json(['message' => 'Diskon berhasil diterapkan'], 200);
        }

        return response()->json(['message' => 'Token tidak valid'], 401);
    }
}