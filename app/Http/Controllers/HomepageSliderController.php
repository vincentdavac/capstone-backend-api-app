<?php

namespace App\Http\Controllers;

use App\Models\HomepageSlider;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Http\Requests\StoreHomepageSlider;
use App\Http\Requests\UpdateHomepageSlider;
use App\Http\Resources\HomepageSliderResource;
use App\Traits\HttpResponses;

class HomepageSliderController extends Controller
{
    use HttpResponses;

    public function index()
    {
        $sliders = HomepageSlider::latest()->get();
        return $this->success(
            HomepageSliderResource::collection($sliders),
            'Homepage sliders fetched successfully',
            200
        );
    }

    public function activeSliders()
    {
        $sliders = HomepageSlider::where('is_archive', false)
            ->latest()
            ->get();

        return $this->success(
            HomepageSliderResource::collection($sliders),
            'Active homepage sliders fetched successfully',
            200
        );
    }

    public function archivedSliders()
    {
        $sliders = HomepageSlider::where('is_archive', true)
            ->latest()
            ->get();

        return $this->success(
            HomepageSliderResource::collection($sliders),
            'Archived homepage sliders fetched successfully',
            200
        );
    }

    public function store(StoreHomepageSlider $request)
    {
        $validated = $request->validated();

        // Handle image upload if provided
        if ($request->hasFile('image')) {
            $imageFile = $request->file('image');
            $imageName = Str::random(32) . '.' . $imageFile->getClientOriginalExtension();
            $imageFile->move(public_path('homepage_slider'), $imageName);
            $validated['image'] = $imageName;
        }

        $slider = HomepageSlider::create($validated);

        return $this->success(
            new HomepageSliderResource($slider),
            'Homepage slider created successfully',
            201
        );
    }

    public function show(HomepageSlider $slider)
    {
        return $this->success(
            new HomepageSliderResource($slider),
            'Homepage slider retrieved successfully',
            200
        );
    }

    public function update(UpdateHomepageSlider $request, HomepageSlider $slider)
    {
        $validated = $request->validated();

        // Handle image replacement if provided
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($slider->image && file_exists(public_path('homepage_slider/' . $slider->image))) {
                unlink(public_path('homepage_slider/' . $slider->image));
            }

            // Upload new image
            $imageFile = $request->file('image');
            $imageName = Str::random(32) . '.' . $imageFile->getClientOriginalExtension();
            $imageFile->move(public_path('homepage_slider'), $imageName);
            $validated['image'] = $imageName;
        }

        $slider->update($validated);

        return $this->success(
            new HomepageSliderResource($slider),
            'Homepage slider updated successfully',
            200
        );
    }

    public function destroy(HomepageSlider $slider)
    {
        // Delete the image file if it exists
        if ($slider->image && file_exists(public_path('homepage_slider/' . $slider->image))) {
            unlink(public_path('homepage_slider/' . $slider->image));
        }

        $slider->delete();

        return $this->success('', 'Homepage slider deleted successfully', 200);
    }
}
