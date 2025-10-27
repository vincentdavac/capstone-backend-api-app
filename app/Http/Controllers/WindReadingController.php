<?php

namespace App\Http\Controllers;

use App\Models\WindReading;
use App\Http\Requests\StoreWindReadingRequest;
use App\Http\Requests\UpdateWindReadingRequest;
use App\Http\Resources\WindReadingResource;
use App\Traits\HttpResponses;

class WindReadingController extends Controller
{
    use HttpResponses;

    public function index()
    {
        return WindReadingResource::collection(WindReading::all());
    }

    public function store(StoreWindReadingRequest $request)
    {
        $validated = $request->validated();
        $reading = WindReading::create($validated);

        return (new WindReadingResource($reading))
            ->response()
            ->setStatusCode(201);
    }

    public function show(WindReading $windReading)
    {
        return new WindReadingResource($windReading);
    }

    public function update(UpdateWindReadingRequest $request, WindReading $windReading)
    {
        $windReading->update($request->validated());
        return new WindReadingResource($windReading);
    }

    public function destroy(WindReading $windReading)
    {
        $windReading->delete();
        return $this->success('', 'Wind reading deleted successfully', 200);
    }
}
