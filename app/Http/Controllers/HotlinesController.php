<?php

namespace App\Http\Controllers;

use App\Models\Hotlines;
use App\Http\Requests\HotlinesRequest;
use App\Http\Resources\HotlinesResource;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;


class HotlinesController extends Controller
{
    use HttpResponses;

    public function index(Request $request)
    {
        // Authenticate user first
        $user = Auth::user();

        if (!$user) {
            return $this->error(null, 'Unauthenticated.', 401);
        }

        $hotlines = Hotlines::with('barangay')
            ->where('is_archived', false) // Only non-archived hotlines
            ->when($user->user_type === 'barangay', function ($query) use ($user) {
                $query->where(function ($q) use ($user) {
                    $q->where('created_by_role', 'admin') // Admin-created visible to barangay
                        ->orWhere('barangay_id', $user->barangay_id); // Barangay's own hotlines
                });
            })
            ->latest()
            ->get();

        return $this->success(
            HotlinesResource::collection($hotlines),
            'Hotlines retrieved successfully.'
        );
    }

    /**
     * Store a newly created hotline
     */
    public function store(HotlinesRequest $request)
    {
        // Authenticate user first
        $user = Auth::user();

        if (!$user) {
            return $this->error(null, 'Unauthenticated.', 401);
        }

        $data = $request->validated();

        // Save who created the hotline
        $data['created_by_role'] = $user->user_type;

        // Barangay can only create hotline for itself
        if ($user->user_type === 'barangay') {
            $data['barangay_id'] = $user->barangay_id; // Use authenticated user's barangay_id
        }

        // Admin can create global or barangay-specific hotline
        if ($user->user_type === 'admin') {
            $data['barangay_id'] =  null;
        }

        $hotline = Hotlines::create($data);

        return $this->success(
            new HotlinesResource($hotline->load('barangay')),
            'Hotline created successfully.',
            201
        );
    }



    /**
     * Display the specified hotline
     */
    public function show(Hotlines $hotline)
    {
        //  Authenticate user first
        if (!Auth::check()) {
            return $this->error(null, 'Unauthenticated.', 401);
        }

        return $this->success(
            new HotlinesResource($hotline->load('barangay')),
            'Hotline retrieved successfully.'
        );
    }

    /**
     * Update the specified hotline
     */
    public function update(HotlinesRequest $request, Hotlines $hotline)
    {
        //  Authenticate user first
        $user = Auth::user();

        if (!$user) {
            return $this->error(null, 'Unauthenticated.', 401);
        }

        // Barangay: can only update its own hotline
        if ($user->user_type === 'barangay') {
            if ($hotline->created_by_role !== 'barangay' || $hotline->barangay_id !== $user->barangay_id) {
                return $this->error(null, 'Unauthorized action.', 403);
            }
        }

        //  Admin: can only update admin-created hotlines
        if ($user->user_type === 'admin') {
            if ($hotline->created_by_role !== 'admin') {
                return $this->error(null, 'Unauthorized action.', 403);
            }
        }

        $hotline->update($request->validated());

        return $this->success(
            new HotlinesResource($hotline->fresh()->load('barangay')),
            'Hotline updated successfully.'
        );
    }


    public function archive(Hotlines $hotline)
    {
        //  Authenticate user first
        $user = Auth::user();

        if (!$user) {
            return $this->error(null, 'Unauthenticated.', 401);
        }

        //  Barangay: can only archive its own hotlines
        if ($user->user_type === 'barangay') {
            if ($hotline->created_by_role !== 'barangay' || $hotline->barangay_id !== $user->barangay_id) {
                return $this->error(null, 'Unauthorized action.', 403);
            }
        }

        //  Admin: can only archive admin-created hotlines
        if ($user->user_type === 'admin') {
            if ($hotline->created_by_role !== 'admin') {
                return $this->error(null, 'Unauthorized action.', 403);
            }
        }

        // Mark as archived
        $hotline->update(['is_archived' => true]);

        return $this->success(
            new HotlinesResource($hotline->fresh()->load('barangay')),
            'Hotline archived successfully.'
        );
    }

    public function restoreArchive(Hotlines $hotline)
    {
        // Authenticate user first
        $user = Auth::user();

        if (!$user) {
            return $this->error(null, 'Unauthenticated.', 401);
        }

        // Barangay: can only restore its own hotlines
        if ($user->user_type === 'barangay') {
            if ($hotline->created_by_role !== 'barangay' || $hotline->barangay_id !== $user->barangay_id) {
                return $this->error(null, 'Unauthorized action.', 403);
            }
        }

        // Admin: can only restore admin-created hotlines
        if ($user->user_type === 'admin') {
            if ($hotline->created_by_role !== 'admin') {
                return $this->error(null, 'Unauthorized action.', 403);
            }
        }

        // Mark as active (restore)
        $hotline->update(['is_archived' => false]);

        return $this->success(
            new HotlinesResource($hotline->fresh()->load('barangay')),
            'Hotline restored successfully.'
        );
    }


    public function archived(Request $request)
    {
        Log::info(' Archived hotlines endpoint HIT');

        // Check authentication
        $user = Auth::user();

        Log::info('👤 Auth user:', [
            'user' => $user
        ]);

        if (!$user) {
            Log::warning('❌ User is unauthenticated');
            return $this->error(null, 'Unauthenticated.', 401);
        }

        Log::info('✅ User authenticated', [
            'user_id'   => $user->id,
            'user_type' => $user->user_type,
            'barangay_id' => $user->barangay_id,
        ]);

        // Base query
        $query = Hotlines::with('barangay')
            ->where('is_archived', true);

        Log::info('📦 Base archived query initialized');

        // Role-based filtering
        if ($user->user_type === 'admin') {
            Log::info('🔐 Admin filtering applied');
            $query->where('created_by_role', 'admin');
        }

        if ($user->user_type === 'barangay') {
            Log::info('🏘 Barangay filtering applied');
            $query->where('created_by_role', 'barangay')
                ->where('barangay_id', $user->barangay_id);
        }

        // Execute query
        $archivedHotlines = $query->latest()->get();

        Log::info('📤 Archived hotlines fetched', [
            'count' => $archivedHotlines->count()
        ]);

        return $this->success(
            HotlinesResource::collection($archivedHotlines),
            'Archived hotlines retrieved successfully.'
        );
    }


    public function userHotlines(Request $request)
    {
        // Authenticate user first
        $user = Auth::user();

        if (!$user) {
            return $this->error(null, 'Unauthenticated.', 401);
        }

        // Only active hotlines
        $hotlines = Hotlines::with('barangay')
            ->where('is_archived', false)
            ->when($user->user_type === 'barangay', function ($query) use ($user) {
                $query->where(function ($q) use ($user) {
                    // Admin-created hotlines (global)
                    $q->where('created_by_role', 'admin')
                        // Barangay's own hotlines
                        ->orWhere('barangay_id', $user->barangay_id);
                });
            })
            ->when($user->user_type === 'admin', function ($query) use ($user) {
                // Admin sees only admin-created hotlines
                $query->where('created_by_role', 'admin');
            })
            ->latest()
            ->get();

        return $this->success(
            HotlinesResource::collection($hotlines),
            'Hotlines retrieved successfully.'
        );
    }



    /**
     * Archive the specified hotline (soft delete behavior)
     */
    public function destroy(Hotlines $hotline)
    {
        //  Authenticate user first
        $user = Auth::user();

        if (!$user) {
            return $this->error(null, 'Unauthenticated.', 401);
        }

        // Barangay can only archive its own hotline
        if (
            $user->user_type === 'barangay' &&
            $hotline->barangay_id !== $user->barangay_id
        ) {
            return $this->error(null, 'Unauthorized action.', 403);
        }

        $hotline->update(['is_archive' => true]);

        return $this->success(
            null,
            'Hotline archived successfully.'
        );
    }
}
