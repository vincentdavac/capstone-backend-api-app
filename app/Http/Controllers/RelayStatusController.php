<?php

namespace App\Http\Controllers;

use App\Models\RelayStatus;
use App\Http\Requests\StoreRelayStatusRequest;
use App\Http\Requests\UpdateRelayStatusRequest;
use App\Http\Resources\RelayStatusResource;
use App\Traits\HttpResponses;

class RelayStatusController extends Controller
{
    use HttpResponses;

    public function index()
    {
        return RelayStatusResource::collection(RelayStatus::all());
    }

    public function store(StoreRelayStatusRequest $request)
    {
        $validated = $request->validated();
        $relay = RelayStatus::create($validated);

        return (new RelayStatusResource($relay))
            ->response()
            ->setStatusCode(201);
    }

    public function show(RelayStatus $relayStatus)
    {
        return new RelayStatusResource($relayStatus);
    }

    public function update(UpdateRelayStatusRequest $request, RelayStatus $relayStatus)
    {
        $relayStatus->update($request->validated());
        return new RelayStatusResource($relayStatus);
    }

    public function destroy(RelayStatus $relayStatus)
    {
        $relayStatus->delete();
        return $this->success('', 'Relay status deleted successfully', 200);
    }
}
