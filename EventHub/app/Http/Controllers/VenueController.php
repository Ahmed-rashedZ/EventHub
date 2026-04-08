<?php

namespace App\Http\Controllers;

use App\Models\Venue;
use Illuminate\Http\Request;

class VenueController extends Controller
{
    // Public list of venues
    public function index(Request $request)
    {
        $user = auth('sanctum')->user();
        if ($user && $user->role === 'Admin') {
            return response()->json(Venue::orderBy('name')->get());
        }
        return response()->json(Venue::where('status', 'available')->orderBy('name')->get());
    }

    public function store(Request $request)
    {
        $this->requireRole($request, 'Admin');

        $request->validate([
            'name'     => 'required|string|max:255',
            'location' => 'required|url|max:500',
            'capacity' => 'required|integer|min:1',
        ]);

        // Unique name check
        if (Venue::where('name', $request->name)->exists()) {
            return response()->json(['message' => 'Venue name already exists'], 422);
        }

        $venue = Venue::create($request->only('name', 'location', 'capacity', 'status'));
        return response()->json($venue, 201);
    }

    public function update(Request $request, $id)
    {
        $this->requireRole($request, 'Admin');

        $venue = Venue::findOrFail($id);
        $venue->update($request->only('name', 'location', 'capacity', 'status'));
        return response()->json($venue);
    }

    public function destroy(Request $request, $id)
    {
        return response()->json(['message' => 'Venue deletion is disabled to preserve archive records'], 403);
    }

    private function requireRole(Request $request, string $role)
    {
        if ($request->user()->role !== $role) {
            abort(403, 'Unauthorized');
        }
    }
}
