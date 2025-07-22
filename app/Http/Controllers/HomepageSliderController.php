<?php

namespace App\Http\Controllers;

use App\Models\HomepageSlider;
use Illuminate\Http\Request;

class HomepageSliderController extends Controller
{
    public function index()
    {
        return HomepageSlider::all();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'image' => 'string|required',
            'image_url' => 'nullable|string',
            'title' => 'required|string',
            'description' => 'nullable|string',
            'is_active' => 'required|boolean',
        ]);

        return HomepageSlider::create($validated);
    }

    public function show($id)
    {
        return HomepageSlider::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $slider = HomepageSlider::findOrFail($id);
        $slider->update($request->all());
        return $slider;
    }

    public function destroy($id)
    {
        $slider = HomepageSlider::findOrFail($id);
        $slider->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
