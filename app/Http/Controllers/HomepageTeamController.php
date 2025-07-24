<?php

namespace App\Http\Controllers;

use App\Models\HomepageTeam;
use Illuminate\Http\Request;
use App\Http\Requests\StoreHomepageTeam;
use App\Http\Resources\HomepageTeamResource;
use App\Traits\HttpResponses;

class HomepageTeamController extends Controller
{
    use HttpResponses;

    public function index()
    {
        return HomepageTeamResource::collection(HomepageTeam::all());
    }

    public function store(StoreHomepageTeam $request)
    {
        $validated = $request->validated();
        $team = HomepageTeam::create($validated);

        return new HomepageTeamResource($team);
    }

    public function show(HomepageTeam $homepageTeam)
    {
        return new HomepageTeamResource($homepageTeam);
    }

    public function update(Request $request, HomepageTeam $homepageTeam)
    {
        $homepageTeam->update($request->all());

        return new HomepageTeamResource($homepageTeam);
    }

    public function destroy(HomepageTeam $homepageTeam)
    {
        $homepageTeam->delete();

        return $this->success('', 'Team member deleted successfully', 200);
    }
}
