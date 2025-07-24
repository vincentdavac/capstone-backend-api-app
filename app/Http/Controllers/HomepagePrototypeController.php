<?php

namespace App\Http\Controllers;

use App\Models\HomepagePrototype;
use Illuminate\Http\Request;
use App\Http\Requests\StoreHomepagePrototype;
use App\Http\Resources\HomepagePrototypeResource;
use App\Traits\HttpResponses;

class HomepagePrototypeController extends Controller
{
    use HttpResponses;

    public function index()
    {
        return HomepagePrototypeResource::collection(HomepagePrototype::all());
    }

    public function store(StoreHomepagePrototype $request)
    {
        $validated = $request->validated();
        $prototype = HomepagePrototype::create($validated);

        return new HomepagePrototypeResource($prototype);
    }

    public function show(HomepagePrototype $homepagePrototype)
    {
        return new HomepagePrototypeResource($homepagePrototype);
    }

    public function update(Request $request, HomepagePrototype $homepagePrototype)
    {
        $homepagePrototype->update($request->all());

        return new HomepagePrototypeResource($homepagePrototype);
    }

    public function destroy(HomepagePrototype $homepagePrototype)
    {
        $homepagePrototype->delete();

        return $this->success('', 'Prototype deleted successfully', 200);
    }
}
