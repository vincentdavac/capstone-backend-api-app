<?php

namespace App\Http\Controllers;

use App\Models\HomepageSlider;
use Illuminate\Http\Request;
use App\Http\Requests\StoreHomepageSlider;
use App\Http\Resources\HomepageSliderResource;
use Illuminate\Support\Facades\Auth;
use App\Traits\HttpResponses;

class HomepageSliderController extends Controller
{

    use HttpResponses;

    public function index()
    {
        return HomepageSliderResource::collection(HomepageSlider::all());
    }

    public function store(StoreHomepageSlider $request)
    {
        $validated = $request->validated();
        $slider = HomepageSlider::create($validated);

        return new HomepageSliderResource($slider);
    }

    public function show(HomepageSlider $slider)
    {
        return new HomepageSliderResource($slider);
    }

    public function update(Request $request,  HomepageSlider $slider)
    {
        $slider->update($request->all());
        return new HomepageSliderResource($slider);
    }

    public function destroy(HomepageSlider $slider)
    {
        $slider->delete();
        return $this->success('', 'Slider deleted successfully', 200);
    }
}
