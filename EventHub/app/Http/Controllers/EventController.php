<?php

namespace App\Http\Controllers;
use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
   
    public function store(Request $request)
    {
        if ($request->user()->role !== 'Event Manager') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $event = Event::create([
            'title' => $request->title,
            'description' => $request->description,
            'venue_id' => $request->venue_id,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'capacity' => $request->capacity,
            'created_by' => $request->user()->id
        ]);

        return response()->json($event);
    }

   
    public function index()
    {
        return Event::where('status', 'approved')->get();
    }

    // 🔥 Admin يوافق
    public function approve($id, Request $request)
    {
        if ($request->user()->role !== 'Admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $event = Event::findOrFail($id);
        $event->status = 'approved';
        $event->save();

        return response()->json(['message' => 'Event approved']);
    }
}
