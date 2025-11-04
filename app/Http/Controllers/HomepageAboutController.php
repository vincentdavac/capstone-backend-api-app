<?php

namespace App\Http\Controllers;

use App\Models\HomepageAbout;
use App\Models\HomepageAboutCard;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Requests\StoreHomepageAbout;
use App\Http\Requests\UpdateHomepageAbout;
use App\Http\Requests\StoreHomepageAboutCardRequest;
use App\Http\Requests\UpdateHomepageAboutCardRequest;
use App\Http\Resources\HomepageAboutResource;
use App\Http\Resources\HomepageAboutCardResource;
use App\Traits\HttpResponses;

class HomepageAboutController extends Controller
{
    use HttpResponses;

    /**
     * Display a listing of About sections with cards.
     */
    public function index()
    {
        $abouts = HomepageAbout::with('cards')->latest()->get();

        return $this->success(
            HomepageAboutResource::collection($abouts),
            'Homepage About sections fetched successfully',
            200
        );
    }

    /**
     * Store a newly created About section.
     */
    public function store(StoreHomepageAbout $request)
    {
        $validated = $request->validated();

        // Handle image upload
        if ($request->hasFile('image')) {
            $imageFile = $request->file('image');
            $imageName = Str::random(32) . '.' . $imageFile->getClientOriginalExtension();
            $imageFile->move(public_path('homepage_about_image'), $imageName);
            $validated['image'] = $imageName;
        }

        $about = HomepageAbout::create($validated);

        return $this->success(
            new HomepageAboutResource($about->load('cards')),
            'Homepage About created successfully',
            201
        );
    }

    /**
     * Display a specific About section with its cards.
     */
    public function show(HomepageAbout $about)
    {
        return $this->success(
            new HomepageAboutResource($about->load('cards')),
            'Homepage About fetched successfully',
            200
        );
    }

    /**
     * Update an existing About section.
     */
    public function update(UpdateHomepageAbout $request, HomepageAbout $about)
    {
        $validated = $request->validated();

        // Handle image upload and delete old one
        if ($request->hasFile('image')) {
            // Delete old image if it exists
            if ($about->image && file_exists(public_path('homepage_about_image/' . $about->image))) {
                unlink(public_path('homepage_about_image/' . $about->image));
            }

            $imageFile = $request->file('image');
            $imageName = Str::random(32) . '.' . $imageFile->getClientOriginalExtension();
            $imageFile->move(public_path('homepage_about_image'), $imageName);
            $validated['image'] = $imageName;
        }

        $about->update($validated);

        return $this->success(
            new HomepageAboutResource($about->load('cards')),
            'Homepage About updated successfully',
            200
        );
    }


    /**
     * Remove the specified About section and its image.
     */
    public function destroy(HomepageAbout $about)
    {
        // Delete image if exists
        if ($about->image && file_exists(public_path('homepage_about_image/' . $about->image))) {
            unlink(public_path('homepage_about_image/' . $about->image));
        }

        $about->delete();

        return $this->success('', 'Homepage About deleted successfully', 200);
    }

    // ========================================================
    // CARD FUNCTIONS
    // ========================================================

    /**
     * Store a new card for an About section.
     */
    public function storeCard(StoreHomepageAboutCardRequest $request)
    {
        $validated = $request->validated();
        $card = HomepageAboutCard::create($validated);

        return $this->success(
            new HomepageAboutCardResource($card),
            'Homepage About card created successfully',
            201
        );
    }

    /**
     * Update an existing card.
     */
    public function updateCard(UpdateHomepageAboutCardRequest $request, HomepageAboutCard $card)
    {
        $validated = $request->validated();
        $card->update($validated);

        return $this->success(
            new HomepageAboutCardResource($card),
            'Homepage About card updated successfully',
            200
        );
    }

    /**
     * Delete a specific card.
     */
    public function destroyCard(HomepageAboutCard $card)
    {
        $card->delete();

        return $this->success('', 'Homepage About card deleted successfully', 200);
    }
}
