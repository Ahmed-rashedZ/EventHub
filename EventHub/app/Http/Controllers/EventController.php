<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EventController extends Controller
{
   public function store(Request $request)
{
    $event = Event::create([
        'title' => $request->title,
        'description' => $request->description,
        'venue_id' => $request->venue_id,
        'start_time' => $request->start_time,
        'end_time' => $request->end_time,
        'capacity' => $request->capacity,
        'created_by' => auth()->id()
    ]);

    return response()->json($event);
}
}
