<?php

namespace App\Http\Controllers;

use App\Models\HomepageTeam;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Requests\StoreHomepageTeamRequest;
use App\Http\Requests\UpdateHomepageTeamRequest;
use App\Http\Resources\HomepageTeamResource;
use App\Traits\HttpResponses;

class HomepageTeamController extends Controller
{
    use HttpResponses;

    /**
     * Display all team members.
     */
    public function index()
    {
        $teams = HomepageTeam::latest()->get();

        return $this->success(
            HomepageTeamResource::collection($teams),
            'Homepage teams fetched successfully',
            200
        );
    }

    /**
     * Store a new team member.
     */
    public function store(StoreHomepageTeamRequest $request)
    {
        $validated = $request->validated();

        if ($request->hasFile('image')) {
            $imageFile = $request->file('image');
            $imageName = Str::random(32) . '.' . $imageFile->getClientOriginalExtension();
            $imageFile->move(public_path('homepage_team_images'), $imageName);
            $validated['image'] = $imageName;
        }

        $team = HomepageTeam::create($validated);

        return $this->success(
            new HomepageTeamResource($team),
            'Homepage team member created successfully',
            201
        );
    }

    /**
     * Display a single team member.
     */
    public function show(HomepageTeam $team)
    {
        return $this->success(
            new HomepageTeamResource($team),
            'Homepage team member fetched successfully',
            200
        );
    }

    /**
     * Update an existing team member.
     */
    public function update(UpdateHomepageTeamRequest $request, HomepageTeam $team)
    {
        $validated = $request->validated();

        if ($request->hasFile('image')) {
            // Delete old image if it exists
            if ($team->image && file_exists(public_path('homepage_team_images/' . $team->image))) {
                unlink(public_path('homepage_team_images/' . $team->image));
            }

            // Upload new image
            $imageFile = $request->file('image');
            $imageName = Str::random(32) . '.' . $imageFile->getClientOriginalExtension();
            $imageFile->move(public_path('homepage_team_images'), $imageName);
            $validated['image'] = $imageName;
        }

        $team->update($validated);

        return $this->success(
            new HomepageTeamResource($team),
            'Homepage team member updated successfully',
            200
        );
    }

    /**
     * Delete a team member (and remove image file if exists).
     */
    public function destroy(HomepageTeam $team)
    {
        if ($team->image && file_exists(public_path('homepage_team_images/' . $team->image))) {
            unlink(public_path('homepage_team_images/' . $team->image));
        }

        $team->delete();

        return $this->success('', 'Homepage team member deleted successfully', 200);
    }

    /**
     * Display all active (non-archived) team members.
     */

    public function publicActiveTeams()
    {
        $teams = HomepageTeam::where('is_archived', false)->latest()->get();

        return $this->success(
            HomepageTeamResource::collection($teams),
            'Active homepage teams fetched successfully',
            200
        );
    }

    public function activeTeams()
    {
        $teams = HomepageTeam::where('is_archived', false)->latest()->get();

        return $this->success(
            HomepageTeamResource::collection($teams),
            'Active homepage teams fetched successfully',
            200
        );
    }

    /**
     * Display all archived team members.
     */
    public function archivedTeams()
    {
        $teams = HomepageTeam::where('is_archived', true)->latest()->get();

        return $this->success(
            HomepageTeamResource::collection($teams),
            'Archived homepage teams fetched successfully',
            200
        );
    }
}
