<?php

namespace App\Http\Controllers;

use App\Models\HomepageTeam;
use Illuminate\Http\Request;

class HomepageTeamController extends Controller
{
    public function index()
    {
        return HomepageTeam::all();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'image' => 'required|string|max:255',
            'image_link' => 'nullable|string|max:255',
            'facebook_link' => 'nullable|string|max:255',
            'twitter_link' => 'nullable|string|max:255',
            'linkedin_link' => 'nullable|string|max:255',
            'instagram_link' => 'nullable|string|max:255',
            'is_active' => 'required|boolean',
        ]);

        return HomepageTeam::create($validated);
    }

    public function show($id)
    {
        return HomepageTeam::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $team = HomepageTeam::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'image' => 'required|string|max:255',
            'image_link' => 'nullable|string|max:255',
            'facebook_link' => 'nullable|string|max:255',
            'twitter_link' => 'nullable|string|max:255',
            'linkedin_link' => 'nullable|string|max:255',
            'instagram_link' => 'nullable|string|max:255',
            'is_active' => 'required|boolean',
        ]);

        $team->update($validated);

        return $team;
    }

    public function destroy($id)
    {
        $team = HomepageTeam::findOrFail($id);
        $team->delete();

        return response()->json(['message' => 'Team member deleted']);
    }
}
