<?php

namespace App\Http\Controllers;

use App\Models\HomepageFaq;
use Illuminate\Http\Request;
use App\Http\Requests\StoreHomepageFaqRequest;
use App\Http\Requests\UpdateHomepageFaqRequest;
use App\Http\Resources\HomepageFaqResource;
use App\Traits\HttpResponses;
use Illuminate\Support\Str;

class HomepageFaqController extends Controller
{
    use HttpResponses;

    /**
     * Display all FAQs.
     */
    public function index()
    {
        $faqs = HomepageFaq::latest()->get();

        return $this->success(
            HomepageFaqResource::collection($faqs),
            'Homepage FAQs fetched successfully',
            200
        );
    }

    /**
     * Store a new FAQ.
     */
    public function store(StoreHomepageFaqRequest $request)
    {
        $validated = $request->validated();

        $faq = HomepageFaq::create($validated);

        return $this->success(
            new HomepageFaqResource($faq),
            'FAQ created successfully',
            201
        );
    }

    /**
     * Display a specific FAQ.
     */
    public function show(HomepageFaq $faq)
    {
        return $this->success(
            new HomepageFaqResource($faq),
            'FAQ fetched successfully',
            200
        );
    }

    /**
     * Update an existing FAQ.
     */
    public function update(UpdateHomepageFaqRequest $request, HomepageFaq $faq)
    {
        $validated = $request->validated();

        $faq->update($validated);

        return $this->success(
            new HomepageFaqResource($faq),
            'FAQ updated successfully',
            200
        );
    }

    /**
     * Delete a FAQ.
     */
    public function destroy(HomepageFaq $faq)
    {
        $faq->delete();

        return $this->success('', 'FAQ deleted successfully', 200);
    }

    /**
     * Display all active FAQs.
     */

    public function publicActiveFaqs()
    {
        $faqs = HomepageFaq::where('is_archived', false)->latest()->get();

        return $this->success(
            HomepageFaqResource::collection($faqs),
            'Active FAQs fetched successfully',
            200
        );
    }

    public function activeFaqs()
    {
        $faqs = HomepageFaq::where('is_archived', false)->latest()->get();

        return $this->success(
            HomepageFaqResource::collection($faqs),
            'Active FAQs fetched successfully',
            200
        );
    }

    /**
     * Display all archived FAQs.
     */
    public function archivedFaqs()
    {
        $faqs = HomepageFaq::where('is_archived', true)->latest()->get();

        return $this->success(
            HomepageFaqResource::collection($faqs),
            'Archived FAQs fetched successfully',
            200
        );
    }
}
