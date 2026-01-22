<?php

namespace App\Http\Controllers;

use App\Models\HomepageFeedback;
use Illuminate\Http\Request;
use App\Http\Requests\StoreHomepageFeedbackRequest;
use App\Http\Requests\UpdateHomepageFeedbackRequest;
use App\Http\Resources\HomepageFeedbackResource;
use App\Traits\HttpResponses;
use Illuminate\Support\Facades\Validator;

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

    // ✅ ADD THIS NEW METHOD FOR MOBILE APP
    /**
     * Submit feedback from mobile app
     */
    public function submitFeedback(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            'feedback' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $feedback = HomepageFeedback::create([
                'user_id' => $request->user()->id,
                'rate' => $request->rating,
                'feedback' => $request->feedback,
                'is_archived' => 0,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Thank you for your feedback!',
                'data' => $feedback
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save feedback',
                'error' => $e->getMessage()
            ], 500);
        }
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
    public function publicActiveFeedbacks()
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