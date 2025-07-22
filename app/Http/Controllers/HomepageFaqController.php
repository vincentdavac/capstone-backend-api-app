<?php

namespace App\Http\Controllers;

use App\Models\HomepageFaq;
use Illuminate\Http\Request;

class HomepageFaqController extends Controller
{
    public function index()
    {
        return HomepageFaq::all();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'question' => 'required|string|max:255',
            'answer' => 'required|string|max:255',
            'is_active' => 'required|boolean',
        ]);

        return HomepageFaq::create($validated);
    }

    public function show($id)
    {
        return HomepageFaq::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $faq = HomepageFaq::findOrFail($id);

        $validated = $request->validate([
            'question' => 'required|string|max:255',
            'answer' => 'required|string|max:255',
            'is_active' => 'required|boolean',
        ]);

        $faq->update($validated);

        return $faq;
    }

    public function destroy($id)
    {
        $faq = HomepageFaq::findOrFail($id);
        $faq->delete();

        return response()->json(['message' => 'FAQ deleted successfully']);
    }
}
