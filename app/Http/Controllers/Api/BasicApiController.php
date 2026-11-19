<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BasicApiController extends Controller
{
    // data dummy
    private static $students = [
        ['id' => 101, 'name' => 'Budi Santoso', 'major' => 'Informatika', 'gpa' => 3.75],
        ['id' => 102, 'name' => 'Siti Rahma', 'major' => 'Sistem Informasi', 'gpa' => 3.90],
        ['id' => 103, 'name' => 'Andi Wijaya', 'major' => 'Informatika', 'gpa' => 3.20],
    ];

    // 1. Pengujian Response JSON Sederhana & Status Code
    public function ping()
    {
        return response()->json([
            'status' => 'success',
            'message' => 'API siap melayani permintaan',
            'timestamp' => now()->toIso8601String()
        ], 200);
    }

    // 2. Menangani Query Parameter
    public function index(Request $request)
    {
        $data = collect(self::$students);

        // Menangkap query parameter 'major'
        if ($request->has('major')) {
            $major = $request->query('major');
            $data = $data->where('major', $major);
        }

        // Menangkap query parameter 'min_gpa'
        if ($request->has('min_gpa')) {
            $minGpa = (float) $request->query('min_gpa');
            $data = $data->where('gpa', '>=', $minGpa);
        }

        return response()->json([
            'status' => 'success',
            'total_data' => $data->count(),
            'data' => $data->values()
        ], 200);
    }

    // 3. Menangani Path Parameter & Error 404
    public function show($id)
    {
        $student = collect(self::$students)
            ->firstWhere('id', (int) $id);

        if (!$student) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data mahasiswa dengan ID ' . $id . ' tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $student
        ], 200);
    }

    // 4. Simulasi Pembuatan Data (201 Created & 400 Bad Request)
    public function store(Request $request)
    {
        $name = $request->input('name');
        $major = $request->input('major');
        $gpa = $request->input('gpa');

        // Validasi sederhana: Jika name atau major kosong
        if (!$name || !$major) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Field name dan major wajib diisi'
            ], 400);
        }

        $newStudent = [
            'id' => rand(104, 999),
            'name' => $name,
            'major' => $major,
            'gpa' => (float) ($gpa ?? 0.0)
        ];

        return response()->json([
            'status' => 'success',
            'message' => 'Data mahasiswa berhasil ditambahkan',
            'data' => $newStudent
        ], 201);
    }
}
