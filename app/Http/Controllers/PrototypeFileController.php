<?php

namespace App\Http\Controllers;

use App\Models\PrototypeFile;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Traits\HttpResponses;
use App\Http\Resources\PrototypeFileResource;
use App\Http\Requests\StorePrototypeFileRequest;
use App\Http\Requests\UpdatePrototypeFileRequest;

class PrototypeFileController extends Controller
{
    use HttpResponses;

    /**
     * Display a listing of all prototype files.
     */
    public function index()
    {
        $prototypes = PrototypeFile::latest()->get();

        return $this->success(
            PrototypeFileResource::collection($prototypes),
            'All prototype files fetched successfully',
            200
        );
    }

    /**
     * Store a newly created prototype file.
     */
    public function store(StorePrototypeFileRequest $request)
    {
        $validated = $request->validated();

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $fileName = Str::random(32) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('prototype_files'), $fileName);
            $validated['attachment'] = $fileName;
        }

        $prototype = PrototypeFile::create($validated);

        return $this->success(
            new PrototypeFileResource($prototype),
            'Prototype file uploaded successfully',
            201
        );
    }

    /**
     * Display the specified prototype file.
     */
    public function show(PrototypeFile $prototypeFile)
    {
        return $this->success(
            new PrototypeFileResource($prototypeFile),
            'Prototype file fetched successfully',
            200
        );
    }

    /**
     * Update the specified prototype file.
     */
    public function update(UpdatePrototypeFileRequest $request, PrototypeFile $prototypeFile)
    {
        $validated = $request->validated();

        if ($request->hasFile('attachment')) {
            // Delete old file if it exists
            if ($prototypeFile->attachment && file_exists(public_path('prototype_files/' . $prototypeFile->attachment))) {
                unlink(public_path('prototype_files/' . $prototypeFile->attachment));
            }

            // Upload new file
            $file = $request->file('attachment');
            $fileName = Str::random(32) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('prototype_files'), $fileName);
            $validated['attachment'] = $fileName;
        }

        $prototypeFile->update($validated);

        return $this->success(
            new PrototypeFileResource($prototypeFile),
            'Prototype file updated successfully',
            200
        );
    }

    /**
     * Remove the specified prototype file.
     */
    public function destroy(PrototypeFile $prototypeFile)
    {
        // Delete file if it exists
        if ($prototypeFile->attachment && file_exists(public_path('prototype_files/' . $prototypeFile->attachment))) {
            unlink(public_path('prototype_files/' . $prototypeFile->attachment));
        }

        $prototypeFile->delete();

        return $this->success(
            '',
            'Prototype file deleted successfully',
            200
        );
    }

    /**
     * Display all active (not archived) prototypes.
     */
    public function activePrototypeFile()
    {
        $prototypes = PrototypeFile::where('is_archived', false)->latest()->get();

        return $this->success(
            PrototypeFileResource::collection($prototypes),
            'Active prototype files fetched successfully',
            200
        );
    }

    /**
     * Display all archived prototypes.
     */
    public function archivedPrototypeFile()
    {
        $prototypes = PrototypeFile::where('is_archived', true)->latest()->get();

        return $this->success(
            PrototypeFileResource::collection($prototypes),
            'Archived prototype files fetched successfully',
            200
        );
    }
}
