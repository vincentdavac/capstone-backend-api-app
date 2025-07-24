<?php

namespace App\Http\Controllers;

use App\Models\HomepageAbout;
use Illuminate\Http\Request;
use App\Http\Requests\StoreHomepageAbout;
use App\Http\Resources\HomepageAboutResource;
use App\Traits\HttpResponses;

class HomepageAboutController extends Controller
{
    use HttpResponses;

    public function index()
    {
        return HomepageAboutResource::collection(HomepageAbout::all());
    }

    public function store(StoreHomepageAbout $request)
    {
        $validated = $request->validated();
        $about = HomepageAbout::create($validated);

        return new HomepageAboutResource($about);
    }

    public function show(HomepageAbout $homepageAbout)
    {
        return new HomepageAboutResource($homepageAbout);
    }

    public function update(Request $request, HomepageAbout $homepageAbout)
    {
        $homepageAbout->update($request->all());

        return new HomepageAboutResource($homepageAbout);
    }

    public function destroy(HomepageAbout $homepageAbout)
    {
        $homepageAbout->delete();

        return $this->success('', 'Homepage About deleted successfully', 200);
    }
}
