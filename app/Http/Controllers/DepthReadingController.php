<?php

namespace App\Http\Controllers;

use App\Models\DepthReading;
use App\Http\Requests\StoreDepthReadingRequest;
use App\Http\Requests\UpdateDepthReadingRequest;
use App\Http\Resources\DepthReadingResource;
use App\Traits\HttpResponses;

class DepthReadingController extends Controller
{
    use HttpResponses;

    public function index()
    {
        return DepthReadingResource::collection(DepthReading::all());
    }

    public function store(StoreDepthReadingRequest $request)
    {
        $validated = $request->validated();
        $reading = DepthReading::create($validated);

        return (new DepthReadingResource($reading))
            ->response()
            ->setStatusCode(201);
    }

    public function show(DepthReading $depthReading)
    {
        return new DepthReadingResource($depthReading);
    }

    public function update(UpdateDepthReadingRequest $request, DepthReading $depthReading)
    {
        $depthReading->update($request->validated());
        return new DepthReadingResource($depthReading);
    }

    public function destroy(DepthReading $depthReading)
    {
        $depthReading->delete();
        return $this->success('', 'Depth reading deleted successfully', 200);
    }
}
