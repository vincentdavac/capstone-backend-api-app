<?php

namespace App\Http\Controllers;

use App\Models\Barangay;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Requests\StoreBarangayRequest;
use App\Http\Requests\UpdateBarangayRequest;
use App\Http\Resources\BarangayResource;
use App\Traits\HttpResponses;

class BarangayController extends Controller
{
    use HttpResponses;

    /**
     * Display a listing of barangays.
     */
    public function index()
    {
        $barangays = Barangay::latest()->get();
        return $this->success(
            BarangayResource::collection($barangays),
            'Barangay list retrieved successfully'
        );
    }

    /**
     * Store a newly created barangay.
     */
    public function store(StoreBarangayRequest $request)
    {
        $validated = $request->validated();

        // Compute hectare if square_meter is given
        if (isset($validated['square_meter'])) {
            $validated['hectare'] = $validated['square_meter'] / 10000;
        }

        // Handle image upload
        if ($request->hasFile('attachment')) {
            $imageFile = $request->file('attachment');
            $imageName = Str::random(32) . '.' . $imageFile->getClientOriginalExtension();
            $imageFile->move(public_path('barangay_attachment'), $imageName);
            $validated['attachment'] = $imageName;
        }

        $barangay = Barangay::create($validated);

        return $this->success(
            new BarangayResource($barangay),
            'Barangay created successfully',
            201
        );
    }

    /**
     * Display the specified barangay.
     */
    public function show(Barangay $barangay)
    {
        return $this->success(
            new BarangayResource($barangay),
            'Barangay retrieved successfully'
        );
    }

    /**
     * Update the specified barangay.
     */
    public function update(UpdateBarangayRequest $request, Barangay $barangay)
    {
        $validated = $request->validated();

        // Compute hectare if square_meter is provided
        if (isset($validated['square_meter'])) {
            $validated['hectare'] = $validated['square_meter'] / 10000;
        }

        // Handle new attachment
        if ($request->hasFile('attachment')) {
            // Delete old image if exists
            if ($barangay->attachment && file_exists(public_path('barangay_attachment/' . $barangay->attachment))) {
                unlink(public_path('barangay_attachment/' . $barangay->attachment));
            }

            $imageFile = $request->file('attachment');
            $imageName = Str::random(32) . '.' . $imageFile->getClientOriginalExtension();
            $imageFile->move(public_path('barangay_attachment'), $imageName);
            $validated['attachment'] = $imageName;
        }

        $barangay->update($validated);

        return $this->success(
            new BarangayResource($barangay),
            'Barangay updated successfully'
        );
    }

    /**
     * Remove the specified barangay.
     */
    public function destroy(Barangay $barangay)
    {
        if ($barangay->attachment && file_exists(public_path('barangay_attachment/' . $barangay->attachment))) {
            unlink(public_path('barangay_attachment/' . $barangay->attachment));
        }

        $barangay->delete();

        return $this->success([], 'Barangay deleted successfully');
    }
}


