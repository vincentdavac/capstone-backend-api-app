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

    public function index()
    {
        // Get the latest HomepageAbout with only active cards
        $about = HomepageAbout::with(['cards' => function ($query) {
            $query->where('is_archive', false)->latest();
        }])->latest()->first();

        if (! $about) {
            return $this->success(null, 'No Homepage About section found.', 200);
        }

        return $this->success(
            new HomepageAboutResource($about),
            'Latest Homepage About section with active cards fetched successfully',
            200
        );
    }


    public function store(StoreHomepageAbout $request)
    {
        $validated = $request->validated();

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


    public function show(HomepageAbout $about)
    {
        return $this->success(
            new HomepageAboutResource($about->load('cards')),
            'Homepage About fetched successfully',
            200
        );
    }


    public function update(UpdateHomepageAbout $request, HomepageAbout $about)
    {
        $validated = $request->validated();

        if ($request->hasFile('image')) {
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


    public function destroy(HomepageAbout $about)
    {
        if ($about->image && file_exists(public_path('homepage_about_image/' . $about->image))) {
            unlink(public_path('homepage_about_image/' . $about->image));
        }

        $about->delete();

        return $this->success('', 'Homepage About deleted successfully', 200);
    }



    public function getAllCards()
    {
        // Fetch all cards with their related About section
        $cards = HomepageAboutCard::with('about')
            ->latest()
            ->get();

        return $this->success(
            HomepageAboutCardResource::collection($cards),
            'All Homepage About cards fetched successfully',
            200
        );
    }


    public function getActiveCards()
    {
        // Fetch cards where is_archive = false, include their about section
        $cards = HomepageAboutCard::with('about')
            ->where('is_archive', false)
            ->latest()
            ->get();

        return $this->success(
            HomepageAboutCardResource::collection($cards),
            'Active Homepage About cards fetched successfully',
            200
        );
    }



    public function storeCard(StoreHomepageAboutCardRequest $request)
    {
        $validated = $request->validated();

        // Get the latest HomepageAbout ID
        $latestAbout = HomepageAbout::latest()->first();

        if (!$latestAbout) {
            return $this->error(
                '',
                'No Homepage About section exists to attach a card.',
                400
            );
        }

        // Count the number of active (is_archive = false) cards for this latest HomepageAbout
        $activeCardCount = HomepageAboutCard::where('homepage_about_id', $latestAbout->id)
            ->where('is_archive', false)
            ->count();

        // Maximum allowed active cards = 3
        if ($activeCardCount >= 3) {
            return $this->error(
                '',
                'Maximum of 3 active cards reached for the latest Homepage About section. Archive or delete a card to add a new one.',
                400
            );
        }

        // Attach card to the latest HomepageAbout
        $validated['homepage_about_id'] = $latestAbout->id;

        $card = HomepageAboutCard::create($validated);

        return $this->success(
            new HomepageAboutCardResource($card),
            'Homepage About card created successfully',
            201
        );
    }


    public function updateCard(UpdateHomepageAboutCardRequest $request, HomepageAboutCard $card)
    {
        $validated = $request->validated();

        // Get the latest HomepageAbout ID
        $latestAbout = HomepageAbout::latest()->first();

        if (! $latestAbout) {
            return $this->error(
                '',
                'No Homepage About section exists to assign this card.',
                400
            );
        }

        // Re-assign the card to the latest about section
        $validated['homepage_about_id'] = $latestAbout->id;

        // Check if the request is trying to restore/un-archive the card
        if (isset($validated['is_archive']) && ! $validated['is_archive']) {
            // Count active cards in the latest section, excluding this card
            $activeCardCount = HomepageAboutCard::where('homepage_about_id', $latestAbout->id)
                ->where('is_archive', false)
                ->where('id', '!=', $card->id)
                ->count();

            if ($activeCardCount >= 3) {
                return $this->error(
                    '',
                    'Cannot restore/un-archive this card. Maximum of 3 active cards allowed for the latest section.',
                    400
                );
            }
        }

        // Update card fields including new homepage_about_id
        $card->update($validated);

        return $this->success(
            new HomepageAboutCardResource($card),
            'Homepage About card updated successfully',
            200
        );
    }




    public function destroyCard(HomepageAboutCard $card)
    {
        $card->delete();

        return $this->success('', 'Homepage About card deleted successfully', 200);
    }
}
