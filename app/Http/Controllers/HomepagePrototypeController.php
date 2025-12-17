<?php

namespace App\Http\Controllers;

use App\Models\HomepagePrototype;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Http\Requests\StoreHomepagePrototype;
use App\Http\Requests\UpdateHomepagePrototype;
use App\Http\Resources\HomepagePrototypeResource;
use App\Traits\HttpResponses;

class HomepagePrototypeController extends Controller
{
    use HttpResponses;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $prototypes = HomepagePrototype::latest()->get();

        return $this->success(
            HomepagePrototypeResource::collection($prototypes),
            'Homepage prototypes fetched successfully',
            200
        );
    }


      public function publicFetchLeftPrototypes()
    {
        $leftPrototypes = HomepagePrototype::where('is_archived', false)
            ->where('position', 'left')
            ->latest()
            ->get();

        return $this->success(
            HomepagePrototypeResource::collection($leftPrototypes),
            'Left prototypes fetched successfully',
            200
        );
    }


      public function fetchLeftPrototypes()
    {
        $leftPrototypes = HomepagePrototype::where('is_archived', false)
            ->where('position', 'left')
            ->latest()
            ->get();

        return $this->success(
            HomepagePrototypeResource::collection($leftPrototypes),
            'Left prototypes fetched successfully',
            200
        );
    }

    public function publicFetchRightPrototypes()
    {
        $rightPrototypes = HomepagePrototype::where('is_archived', false)
            ->where('position', 'right')
            ->latest()
            ->get();

        return $this->success(
            HomepagePrototypeResource::collection($rightPrototypes),
            'Right prototypes fetched successfully',
            200
        );
    }

    public function fetchRightPrototypes()
    {
        $rightPrototypes = HomepagePrototype::where('is_archived', false)
            ->where('position', 'right')
            ->latest()
            ->get();

        return $this->success(
            HomepagePrototypeResource::collection($rightPrototypes),
            'Right prototypes fetched successfully',
            200
        );
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreHomepagePrototype $request)
    {
        $validated = $request->validated();

        if ($request->hasFile('image')) {
            $imageFile = $request->file('image');
            $imageName = Str::random(32) . '.' . $imageFile->getClientOriginalExtension();
            $imageFile->move(public_path('homepage_prototype_images'), $imageName);
            $validated['image'] = $imageName;
        }

        $prototype = HomepagePrototype::create($validated);

        return $this->success(
            new HomepagePrototypeResource($prototype),
            'Homepage prototype created successfully',
            201
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(HomepagePrototype $prototype)
    {
        return $this->success(
            new HomepagePrototypeResource($prototype),
            'Homepage prototype fetched successfully',
            200
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateHomepagePrototype $request, HomepagePrototype $prototype)
    {
        $validated = $request->validated();

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if it exists
            if ($prototype->image && file_exists(public_path('homepage_prototype_images/' . $prototype->image))) {
                unlink(public_path('homepage_prototype_images/' . $prototype->image));
            }

            // Upload new image
            $imageFile = $request->file('image');
            $imageName = Str::random(32) . '.' . $imageFile->getClientOriginalExtension();
            $imageFile->move(public_path('homepage_prototype_images'), $imageName);
            $validated['image'] = $imageName;
        }

        // Update record
        $prototype->update($validated);

        return $this->success(
            new HomepagePrototypeResource($prototype),
            'Homepage prototype updated successfully',
            200
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(HomepagePrototype $prototype)
    {
        // Delete the image file if it exists
        if ($prototype->image && file_exists(public_path('homepage_prototype_images/' . $prototype->image))) {
            unlink(public_path('homepage_prototype_images/' . $prototype->image));
        }

        $prototype->delete();

        return $this->success('', 'Homepage prototype deleted successfully', 200);
    }



    public function activePrototypes()
    {
        $active = HomepagePrototype::where('is_archived', false)->latest()->get();

        return $this->success(
            HomepagePrototypeResource::collection($active),
            'Active homepage prototypes fetched successfully',
            200
        );
    }

    /**
     * Display all archived homepage prototypes.
     */
    public function archivedPrototypes()
    {
        $archived = HomepagePrototype::where('is_archived', true)->latest()->get();

        return $this->success(
            HomepagePrototypeResource::collection($archived),
            'Archived homepage prototypes fetched successfully',
            200
        );
    }
}
