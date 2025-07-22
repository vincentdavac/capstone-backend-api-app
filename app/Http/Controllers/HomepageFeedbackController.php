<?php

namespace App\Http\Controllers;

use App\Models\HomepageFeedback;
use Illuminate\Http\Request;

class HomepageFeedbackController extends Controller
{
    public function index()
    {
        return HomepageFeedback::all();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required|string|max:255',
            'image' => 'required|string|max:255',
            'image_link' => 'nullable|string|max:255',
            'rate' => 'required|integer|min:1|max:5',
            'feedback' => 'required|string|max:255',
            'is_active' => 'required|boolean',
        ]);

        return HomepageFeedback::create($validated);
    }

    public function show($id)
    {
        return HomepageFeedback::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $feedback = HomepageFeedback::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required|string|max:255',
            'image' => 'required|string|max:255',
            'image_link' => 'nullable|string|max:255',
            'rate' => 'required|integer|min:1|max:5',
            'feedback' => 'required|string|max:255',
            'is_active' => 'required|boolean',
        ]);

        $feedback->update($validated);

        return $feedback;
    }

    public function destroy($id)
    {
        $feedback = HomepageFeedback::findOrFail($id);
        $feedback->delete();

        return response()->json(['message' => 'Deleted successfully.']);
    }
}
