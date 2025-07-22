<?php

namespace App\Http\Controllers;

use App\Models\HomepagePrototype;
use Illuminate\Http\Request;

class HomepagePrototypeController extends Controller
{
    public function index()
    {
        return HomepagePrototype::all();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'caption' => 'nullable|string|max:255',
            'image' => 'required|string|max:255',
            'image_link' => 'nullable|string|max:255',
            'is_active' => 'required|boolean',
        ]);

        return HomepagePrototype::create($validated);
    }

    public function show($id)
    {
        return HomepagePrototype::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $prototype = HomepagePrototype::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'caption' => 'nullable|string|max:255',
            'image' => 'required|string|max:255',
            'image_link' => 'nullable|string|max:255',
            'is_active' => 'required|boolean',
        ]);

        $prototype->update($validated);

        return $prototype;
    }

    public function destroy($id)
    {
        $prototype = HomepagePrototype::findOrFail($id);
        $prototype->delete();

        return response()->json(['message' => 'Prototype deleted']);
    }
}
