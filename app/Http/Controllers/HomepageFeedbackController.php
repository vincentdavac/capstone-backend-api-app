<?php

namespace App\Http\Controllers;

use App\Models\HomepageFeedback;
use Illuminate\Http\Request;
use App\Http\Requests\StoreHomepageFeedback;
use App\Http\Resources\HomepageFeedbackResource;
use App\Traits\HttpResponses;

class HomepageFeedbackController extends Controller
{
    use HttpResponses;

    public function index()
    {
        return HomepageFeedbackResource::collection(HomepageFeedback::all());
    }

    public function store(StoreHomepageFeedback $request)
    {
        $validated = $request->validated();
        $feedback = HomepageFeedback::create($validated);

        return new HomepageFeedbackResource($feedback);
    }

    public function show(HomepageFeedback $homepageFeedback)
    {
        return new HomepageFeedbackResource($homepageFeedback);
    }

    public function update(Request $request, HomepageFeedback $homepageFeedback)
    {
        $homepageFeedback->update($request->all());

        return new HomepageFeedbackResource($homepageFeedback);
    }

    public function destroy(HomepageFeedback $homepageFeedback)
    {
        $homepageFeedback->delete();

        return $this->success('', 'Feedback deleted successfully', 200);
    }
}
