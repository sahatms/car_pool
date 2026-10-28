<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Borrower;
use Illuminate\Http\Request;

class BorrowerController extends Controller
{
    public function index()
    {
        return response()->json([
            'message' => 'Daftar peminjam berhasil diambil.',
            'data' => Borrower::latest()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:150'],
            'employee_number' => ['nullable', 'string', 'max:50'],
            'department' => ['required', 'string', 'max:100'],
            'phone_number' => ['required', 'string', 'max:20'],
            'selfie' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
        ]);

        if ($request->hasFile('selfie')) {
            $validated['selfie_path'] = $request->file('selfie')
                ->store('borrowers/selfies', 'public');
        }

        unset($validated['selfie']);

        $borrower = Borrower::create($validated);

        return response()->json([
            'message' => 'Data peminjam berhasil ditambahkan.',
            'data' => $borrower,
        ], 201);
    }

    public function show(string $id)
    {
        $borrower = Borrower::findOrFail($id);

        return response()->json([
            'message' => 'Detail peminjam berhasil diambil.',
            'data' => $borrower,
        ]);
    }
}
