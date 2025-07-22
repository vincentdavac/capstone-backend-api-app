<?php

namespace App\Http\Controllers;

use App\Models\HomepageFooter;
use Illuminate\Http\Request;

class HomepageFooterController extends Controller
{
    public function index()
    {
        return HomepageFooter::all();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'footer_text' => 'nullable|string|max:255',
            'facebook_url' => 'nullable|url|max:255',
            'twitter_url' => 'nullable|url|max:255',
            'instagram_url' => 'nullable|url|max:255',
            'linkedin_url' => 'nullable|url|max:255',
            'is_active' => 'required|boolean',
        ]);

        return HomepageFooter::create($validated);
    }

    public function show($id)
    {
        return HomepageFooter::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $footer = HomepageFooter::findOrFail($id);

        $validated = $request->validate([
            'footer_text' => 'nullable|string|max:255',
            'facebook_url' => 'nullable|url|max:255',
            'twitter_url' => 'nullable|url|max:255',
            'instagram_url' => 'nullable|url|max:255',
            'linkedin_url' => 'nullable|url|max:255',
            'is_active' => 'required|boolean',
        ]);

        $footer->update($validated);

        return $footer;
    }

    public function destroy($id)
    {
        $footer = HomepageFooter::findOrFail($id);
        $footer->delete();

        return response()->json(['message' => 'Footer deleted']);
    }
}
