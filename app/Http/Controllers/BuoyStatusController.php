<?php

namespace App\Http\Controllers;

use App\Models\BuoyStatus;
use App\Http\Requests\StoreBuoyStatusRequest;
use App\Http\Requests\UpdateBuoyStatusRequest;
use App\Http\Resources\BuoyStatusResource;
use App\Traits\HttpResponses;

class BuoyStatusController extends Controller
{
    use HttpResponses;

    public function index()
    {
        return BuoyStatusResource::collection(BuoyStatus::all());
    }

    public function store(StoreBuoyStatusRequest $request)
    {
        $validated = $request->validated();
        $status = BuoyStatus::create($validated);

        return new BuoyStatusResource($status);
    }

    public function show(BuoyStatus $buoyStatus)
    {
        return new BuoyStatusResource($buoyStatus);
    }

    public function update(UpdateBuoyStatusRequest $request, BuoyStatus $buoyStatus)
    {
        $buoyStatus->update($request->validated());
        return new BuoyStatusResource($buoyStatus);
    }

    public function destroy(BuoyStatus $buoyStatus)
    {
        $buoyStatus->delete();
        return $this->success('', 'Buoy status deleted successfully', 200);
    }
}
