<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:50',
            'slug' => 'required|string|max:50|unique:categories,slug',
            'description' => 'nullable|string',
        ]);

        try {
            Category::create([
                'name' => $request->name,
                'slug' => $request->slug,
                'description' => $request->description,
            ]);
        } catch (\Exception $e) {
            return redirect()->route('admin.index')->with('error', 'Error creating category: '.$e->getMessage());
        }

        return redirect()->route('admin.index')->with('success', 'Categoría creada exitosamente.');
    }

    public function quickStore(Request $request)
    {
        // Validamos que el nombre no venga vacío y no exista ya
        $request->validate([
            'name' => 'required|string|max:50|unique:categories,name',
        ]);

        // Creamos la categoría
        $category = Category::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'is_active' => true,
        ]);

        // Retornamos la nueva categoría en formato JSON
        return response()->json([
            'success' => true,
            'category' => $category,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|string|max:50',
            'slug' => 'required|string|max:50|unique:categories,slug,'.$id,
            'description' => 'nullable|string',
        ]);

        $category = Category::findOrFail($id);
        $category->update([
            'name' => $request->name,
            'slug' => $request->slug,
            'description' => $request->description,
        ]);

        return redirect()->route('admin.index')->with('success', 'Categoría actualizada exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $category = Category::findOrFail($id);
        $category->delete();

        return redirect()->route('admin.index')->with('success', 'Categoría eliminada exitosamente.');
    }
}
