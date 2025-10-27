<?php

namespace App\Http\Controllers;

use App\Models\Mpu6050Reading;
use App\Http\Requests\StoreMpu6050ReadingRequest;
use App\Http\Requests\UpdateMpu6050ReadingRequest;
use App\Http\Resources\Mpu6050ReadingResource;
use App\Traits\HttpResponses;

class Mpu6050ReadingController extends Controller
{
    use HttpResponses;

    public function index()
    {
        return Mpu6050ReadingResource::collection(Mpu6050Reading::all());
    }

    public function store(StoreMpu6050ReadingRequest $request)
    {
        $validated = $request->validated();
        $mpu = Mpu6050Reading::create($validated);
        return (new Mpu6050ReadingResource($mpu))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Mpu6050Reading $mpu6050Reading)
    {
        return new Mpu6050ReadingResource($mpu6050Reading);
    }

    public function update(UpdateMpu6050ReadingRequest $request, Mpu6050Reading $mpu6050Reading)
    {
        $mpu6050Reading->update($request->validated());
        return new Mpu6050ReadingResource($mpu6050Reading);
    }

    public function destroy(Mpu6050Reading $mpu6050Reading)
    {
        $mpu6050Reading->delete();
        return $this->success('', 'MPU6050 reading deleted successfully', 200);
    }
}
