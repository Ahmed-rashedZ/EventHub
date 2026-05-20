<?php

namespace App\Http\Controllers;

use App\Models\ExhibitionApplication;
use App\Models\Event;
use Illuminate\Http\Request;

class CompanyAnalyticsController extends Controller
{
    // GET /api/company/analytics
    public function overview(Request $request)
    {
        $user = $request->user();
        if ($user->role !== 'Company') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $apps = ExhibitionApplication::where('company_id', $user->id)->get();
        $total = $apps->count();
        $accepted = $apps->where('status', 'accepted')->count() + $apps->where('status', 'negotiating')->count();
        $rejected = $apps->where('status', 'rejected')->count();
        $pending = $apps->where('status', 'pending')->count();


        // Profile completion check
        $profile = $user->profile;
        $completionFields = ['bio', 'company_description', 'logo'];
        $filled = 0;
        $totalFields = count($completionFields) + 3; // + name, email, verification
        if ($user->name) $filled++;
        if ($user->email) $filled++;
        if ($user->verification_status === 'verified') $filled++;
        if ($profile) {
            foreach ($completionFields as $f) {
                if (!empty($profile->$f)) $filled++;
            }
        }
        $profileCompletion = round(($filled / $totalFields) * 100);

        // Upcoming exhibitions
        $upcomingApps = ExhibitionApplication::with(['event.venue'])
            ->where('company_id', $user->id)
            ->whereIn('status', ['accepted', 'negotiating'])
            ->whereHas('event', function ($q) {
                $q->where('start_time', '>', now());
            })
            ->get()
            ->map(function ($app) {
                return [
                    'event_id'           => $app->event_id,
                    'title'              => $app->event->title,
                    'start_time'         => $app->event->start_time,
                    'application_status' => $app->status,
                    'agreement_status'   => $app->negotiation?->status,
                ];
            });

        // Past exhibitions
        $pastApps = ExhibitionApplication::with(['event.venue'])
            ->where('company_id', $user->id)
            ->whereHas('event', function ($q) {
                $q->where('end_time', '<', now());
            })
            ->get()
            ->map(function ($app) {
                return [
                    'event_id'           => $app->event_id,
                    'title'              => $app->event->title,
                    'start_time'         => $app->event->start_time,
                    'application_status' => $app->status,
                ];
            });

        // Application history
        $history = ExhibitionApplication::with(['event'])
            ->where('company_id', $user->id)
            ->latest()
            ->get()
            ->map(function ($app) {
                return [
                    'application_id' => $app->id,
                    'event_id'       => $app->event_id,
                    'initiator'      => $app->initiator,
                    'event_title'    => $app->event->title ?? '—',
                    'submitted_at'   => $app->created_at->format('Y-m-d'),
                    'status'         => $app->status,
                ];
            });

        return response()->json([
            'profile_completion'   => $profileCompletion,
            'total_applications'   => $total,
            'accepted'             => $accepted,
            'rejected'             => $rejected,
            'pending'              => $pending,
            'acceptance_rate'      => $total > 0 ? round(($accepted / $total) * 100, 1) : 0,
            'upcoming_exhibitions' => $upcomingApps,
            'past_exhibitions'     => $pastApps,
            'application_history'  => $history,
        ]);
    }

    // GET /api/company/exhibitions
    public function myExhibitions(Request $request)
    {
        $user = $request->user();
        if ($user->role !== 'Company') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $apps = ExhibitionApplication::with(['event.venue', 'negotiation'])
            ->where('company_id', $user->id)
            ->latest()
            ->get();

        return response()->json($apps);
    }
}
