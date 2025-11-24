<?php

namespace App\Http\Controllers;

use App\Models\HomepageFooter;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Http\Requests\StoreHomepageFooterRequest;
use App\Http\Requests\UpdateHomepageFooterRequest;
use App\Http\Resources\HomepageFooterResource;
use App\Traits\HttpResponses;

class HomepageFooterController extends Controller
{
    use HttpResponses;

    public function index()
    {
        $footers = HomepageFooter::where('is_archived', false)->latest()->get();

        return $this->success(
            HomepageFooterResource::collection($footers),
            'Homepage footers fetched successfully',
            200
        );
    }


    public function store(StoreHomepageFooterRequest $request)
    {
        // Check if a footer already exists
        if (HomepageFooter::exists()) {
            return $this->error(
                null,
                'Only one homepage footer is allowed. Please update the existing one.',
                400
            );
        }

        $validated = $request->validated();

        if ($request->hasFile('image')) {
            $imageFile = $request->file('image');
            $imageName = Str::random(32) . '.' . $imageFile->getClientOriginalExtension();
            $imageFile->move(public_path('footer_images'), $imageName);
            $validated['image'] = $imageName;
        }

        $footer = HomepageFooter::create($validated);

        return $this->success(
            new HomepageFooterResource($footer),
            'Homepage footer created successfully',
            201
        );
    }

    public function show(HomepageFooter $footer)
    {
        return $this->success(
            new HomepageFooterResource($footer),
            'Homepage footer fetched successfully',
            200
        );
    }

    public function update(UpdateHomepageFooterRequest $request, HomepageFooter $footer)
    {
        $validated = $request->validated();

        if ($request->hasFile('image')) {
            // Delete old image if it exists
            if ($footer->image && file_exists(public_path('footer_images/' . $footer->image))) {
                unlink(public_path('footer_images/' . $footer->image));
            }

            // Upload new image
            $imageFile = $request->file('image');
            $imageName = Str::random(32) . '.' . $imageFile->getClientOriginalExtension();
            $imageFile->move(public_path('footer_images'), $imageName);
            $validated['image'] = $imageName;
        }

        $footer->update($validated);

        return $this->success(
            new HomepageFooterResource($footer),
            'Homepage footer updated successfully',
            200
        );
    }


    public function destroy(HomepageFooter $footer)
    {
        $footer->delete();

        return $this->success(
            '',
            'Homepage footer deleted successfully',
            200
        );
    }

    public function activeFooters()
    {
        $active = HomepageFooter::where('is_archived', false)->latest()->get();

        return $this->success(
            HomepageFooterResource::collection($active),
            'Active homepage footers fetched successfully',
            200
        );
    }

    public function archivedFooters()
    {
        $archived = HomepageFooter::where('is_archived', true)->latest()->get();

        return $this->success(
            HomepageFooterResource::collection($archived),
            'Archived homepage footers fetched successfully',
            200
        );
    }
}
