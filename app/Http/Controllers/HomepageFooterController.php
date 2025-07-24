<?php

namespace App\Http\Controllers;

use App\Models\HomepageFooter;
use Illuminate\Http\Request;
use App\Http\Requests\StoreHomepageFooter;
use App\Http\Resources\HomepageFooterResource;
use App\Traits\HttpResponses;

class HomepageFooterController extends Controller
{
    use HttpResponses;

    public function index()
    {
        return HomepageFooterResource::collection(HomepageFooter::all());
    }

    public function store(StoreHomepageFooter $request)
    {
        $validated = $request->validated();
        $footer = HomepageFooter::create($validated);

        return new HomepageFooterResource($footer);
    }

    public function show(HomepageFooter $homepageFooter)
    {
        return new HomepageFooterResource($homepageFooter);
    }

    public function update(Request $request, HomepageFooter $homepageFooter)
    {
        $homepageFooter->update($request->all());

        return new HomepageFooterResource($homepageFooter);
    }

    public function destroy(HomepageFooter $homepageFooter)
    {
        $homepageFooter->delete();

        return $this->success('', 'Footer deleted successfully', 200);
    }
}
