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
    public function show(HomepagePrototype $homepagePrototype)
    {
        return $this->success(
            new HomepagePrototypeResource($homepagePrototype),
            'Homepage prototype fetched successfully',
            200
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateHomepagePrototype $request, HomepagePrototype $homepagePrototype)
    {
        $validated = $request->validated();

        if ($request->hasFile('image')) {
            // Delete old image if it exists
            if ($homepagePrototype->image && file_exists(public_path('homepage_prototype_images/' . $homepagePrototype->image))) {
                unlink(public_path('homepage_prototype_images/' . $homepagePrototype->image));
            }

            // Upload new image
            $imageFile = $request->file('image');
            $imageName = Str::random(32) . '.' . $imageFile->getClientOriginalExtension();
            $imageFile->move(public_path('homepage_prototype_images'), $imageName);
            $validated['image'] = $imageName;
        }

        $homepagePrototype->update($validated);

        return $this->success(
            new HomepagePrototypeResource($homepagePrototype),
            'Homepage prototype updated successfully',
            200
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(HomepagePrototype $homepagePrototype)
    {
        // Delete the image file if it exists
        if ($homepagePrototype->image && file_exists(public_path('homepage_prototype_images/' . $homepagePrototype->image))) {
            unlink(public_path('homepage_prototype_images/' . $homepagePrototype->image));
        }

        $homepagePrototype->delete();

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
