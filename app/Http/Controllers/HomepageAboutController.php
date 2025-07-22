<?php

namespace App\Http\Controllers;

use App\Models\HomepageAbout;
use Illuminate\Http\Request;

class HomepageAboutController extends Controller
{
    public function index()
    {
        return HomepageAbout::all();
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'caption' => 'required|string|max:255',
            'image' => 'required|string|max:255',
            'image_link' => 'nullable|string|max:255',
            'side_title' => 'required|string|max:255',
            'side_description' => 'required|string|max:255',
            'first_card_title' => 'required|string|max:255',
            'first_card_description' => 'required|string|max:255',
            'second_card_title' => 'required|string|max:255',
            'second_card_description' => 'required|string|max:255',
            'third_card_title' => 'required|string|max:255',
            'third_card_description' => 'required|string|max:255',
        ]);

        $about = HomepageAbout::create($validated);

        return response()->json(['message' => 'Homepage About created successfully', 'data' => $about], 201);
    }

    public function show(HomepageAbout $homepageAbout)
    {
        return response()->json($homepageAbout);
    }

    public function edit(HomepageAbout $homepageAbout)
    {
        //
    }

    public function update(Request $request, HomepageAbout $homepageAbout)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'caption' => 'required|string|max:255',
            'image' => 'required|string|max:255',
            'image_link' => 'nullable|string|max:255',
            'side_title' => 'required|string|max:255',
            'side_description' => 'required|string|max:255',
            'first_card_title' => 'required|string|max:255',
            'first_card_description' => 'required|string|max:255',
            'second_card_title' => 'required|string|max:255',
            'second_card_description' => 'required|string|max:255',
            'third_card_title' => 'required|string|max:255',
            'third_card_description' => 'required|string|max:255',
        ]);

        $homepageAbout->update($validated);

        return response()->json(['message' => 'Homepage About updated successfully', 'data' => $homepageAbout]);
    }

    public function destroy(HomepageAbout $homepageAbout)
    {
        $homepageAbout->delete();

        return response()->json(['message' => 'Homepage About deleted successfully']);
    }
}
