<?php

namespace App\Http\Controllers;

use App\Models\ExhibitionZone;
use App\Models\ExhibitionBooth;
use App\Models\Event;
use Illuminate\Http\Request;

class ExhibitionInventoryController extends Controller
{
    // GET /api/exhibition/inventory/{eventId}
    public function index($eventId)
    {
        $zones = ExhibitionZone::with('booths.application.company.profile')
            ->where('event_id', $eventId)
            ->get();
        return response()->json($zones);
    }

    // POST /api/exhibition/inventory/{eventId}/zones
    public function storeZone(Request $request, $eventId)
    {
        $request->validate(['name' => 'required|string|max:100']);
        
        $zone = ExhibitionZone::create([
            'event_id' => $eventId,
            'name'     => $request->name
        ]);

        return response()->json($zone, 201);
    }

    // DELETE /api/exhibition/inventory/zones/{id}
    public function destroyZone($id)
    {
        $zone = ExhibitionZone::findOrFail($id);
        $zone->delete();
        return response()->json(['message' => 'Zone deleted']);
    }

    // POST /api/exhibition/inventory/zones/{zoneId}/booths
    public function storeBooth(Request $request, $zoneId)
    {
        $request->validate([
            'booth_number' => 'required|string|max:50',
            'size'         => 'nullable|string|max:50'
        ]);

        $booth = ExhibitionBooth::create([
            'exhibition_zone_id' => $zoneId,
            'booth_number'       => $request->booth_number,
            'size'               => $request->size
        ]);

        return response()->json($booth, 201);
    }

    // PUT /api/exhibition/inventory/booths/{id}
    public function updateBooth(Request $request, $id)
    {
        $booth = ExhibitionBooth::findOrFail($id);
        $request->validate([
            'booth_number' => 'sometimes|required|string|max:50',
            'size'         => 'nullable|string|max:50'
        ]);

        $booth->update($request->all());
        return response()->json($booth);
    }

    // DELETE /api/exhibition/inventory/booths/{id}
    public function destroyBooth($id)
    {
        $booth = ExhibitionBooth::findOrFail($id);
        if ($booth->exhibition_application_id) {
            return response()->json(['message' => 'Cannot delete allocated booth'], 400);
        }
        $booth->delete();
        return response()->json(['message' => 'Booth deleted']);
    }

    // POST /api/exhibition/inventory/zones/{zoneId}/booths/batch
    public function batchGenerateBooths(Request $request, $zoneId)
    {
        $request->validate([
            'prefix' => 'required|string|max:10',
            'start'  => 'required|integer|min:1',
            'count'  => 'required|integer|min:1|max:100',
            'size'   => 'nullable|string|max:50'
        ]);

        $booths = [];
        for ($i = 0; $i < $request->count; $i++) {
            $num = $request->prefix . ($request->start + $i);
            $booths[] = ExhibitionBooth::create([
                'exhibition_zone_id' => $zoneId,
                'booth_number'       => $num,
                'size'               => $request->size
            ]);
        }

        return response()->json($booths, 201);
    }
}
