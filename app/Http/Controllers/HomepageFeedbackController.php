<?php

namespace App\Http\Controllers;

use App\Models\HomepageFeedback;
use Illuminate\Http\Request;
use App\Http\Requests\StoreHomepageFeedbackRequest;
use App\Http\Requests\UpdateHomepageFeedbackRequest;
use App\Http\Resources\HomepageFeedbackResource;
use App\Traits\HttpResponses;

class HomepageFeedbackController extends Controller
{
    use HttpResponses;

    /**
     * Display all feedbacks.
     */
    public function index()
    {
        $feedbacks = HomepageFeedback::latest()->get();

        return $this->success(
            HomepageFeedbackResource::collection($feedbacks),
            'All feedbacks fetched successfully',
            200
        );
    }

    /**
     * Store a newly created feedback.
     */
    public function store(StoreHomepageFeedbackRequest $request)
    {
        $validated = $request->validated();
        $feedback = HomepageFeedback::create($validated);

        return $this->success(
            new HomepageFeedbackResource($feedback),
            'Feedback created successfully',
            201
        );
    }

    /**
     * Display a specific feedback.
     */
    public function show(HomepageFeedback $feedback)
    {
        return $this->success(
            new HomepageFeedbackResource($feedback),
            'Feedback fetched successfully',
            200
        );
    }

    /**
     * Update the specified feedback.
     */
    public function update(UpdateHomepageFeedbackRequest $request, HomepageFeedback $feedback)
    {
        $validated = $request->validated();
        $feedback->update($validated);

        return $this->success(
            new HomepageFeedbackResource($feedback),
            'Feedback updated successfully',
            200
        );
    }

    /**
     * Remove the specified feedback.
     */
    public function destroy(HomepageFeedback $feedback)
    {
        $feedback->delete();

        return $this->success(
            null,
            'Feedback deleted successfully',
            200
        );
    }

    /**
     * Display all active (not archived) feedbacks.
     */
    public function activeFeedbacks()
    {
        $feedback = HomepageFeedback::where('is_archived', false)
            ->latest()
            ->get();

        return $this->success(
            HomepageFeedbackResource::collection($feedback),
            'Active feedbacks fetched successfully',
            200
        );
    }

    /**
     * Display all archived feedbacks.
     */
    public function archivedFeedbacks()
    {
        $feedback = HomepageFeedback::where('is_archived', true)
            ->latest()
            ->get();

        return $this->success(
            HomepageFeedbackResource::collection($feedback),
            'Archived feedbacks fetched successfully',
            200
        );
    }
}
