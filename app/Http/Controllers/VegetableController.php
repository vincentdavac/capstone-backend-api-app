<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVegetableRequest;
use App\Http\Resources\VegetableResource;
use App\Models\Vegetable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use \App\Traits\HttpResponses;

class VegetableController extends Controller
{
    use HttpResponses;

    public function index()
    {
        return VegetableResource::collection(
            Vegetable::where('user_id', Auth::user()->id)->get()
        );
    }


    public function store(StoreVegetableRequest $request)
    {
        $request->validated($request->all());

        $vegetable = Vegetable::create([
            'user_id' => Auth::user()->id,
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return new VegetableResource($vegetable);
    }


    public function show(Vegetable $vegetable)
    {
        return $this->isNotAuthorized($vegetable) ? $this->isNotAuthorized($vegetable) : new VegetableResource($vegetable);
    }


    public function update(Request $request, Vegetable $vegetable)
    {

        $vegetable->update($request->all());

        return new VegetableResource($vegetable);
    }


    public function destroy(Vegetable $vegetable)
    {
        if ($this->isNotAuthorized($vegetable)) {
            return $this->isNotAuthorized($vegetable);
        }
        $vegetable->delete();
        return $this->success('', 'Vegetable deleted successfully', 204);
    }



    private function isNotAuthorized($vegetable)
    {
        if (Auth::user()->id !== $vegetable->user_id) {
            return $this->error('', 'You are unauthorized to make this request', 403);
        }
    }
}
