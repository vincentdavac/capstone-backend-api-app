<?php

namespace App\Http\Controllers;

use App\Models\Buoy;
use App\Http\Requests\StoreBuoyRequest;
use App\Http\Requests\UpdateBuoyRequest;
use App\Http\Resources\BuoyResource;
use App\Traits\HttpResponses;

class BuoyController extends Controller
{
    use HttpResponses;

    public function index()
    {
        return BuoyResource::collection(Buoy::all());
    }

    public function store(StoreBuoyRequest $request)
    {
        $validated = $request->validated();
        $buoy = Buoy::create($validated);

        return new BuoyResource($buoy);
    }

    public function show(Buoy $buoy)
    {
        return new BuoyResource($buoy);
    }

    public function update(UpdateBuoyRequest $request, Buoy $buoy)
    {
        $buoy->update($request->validated());
        return new BuoyResource($buoy);
    }

    public function destroy(Buoy $buoy)
    {
        $buoy->delete();
        return $this->success('', 'Buoy deleted successfully', 200);
    }
}
