<?php

namespace Tests\Unit\Event;

use App\Http\Controllers\EventController;
use App\Models\Event;
use App\Models\User;
use App\Notifications\SystemNotification;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\CreatesEventLifecycleSchema;
use Tests\TestCase;

class EventLifecycleTest extends TestCase
{
    use CreatesEventLifecycleSchema;

    private EventController $controller;
    private User $manager;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpEventLifecycleSchema();
        Storage::fake('public');
        Notification::fake();

        $this->controller = new EventController();
        $this->manager = User::create([
            'name' => 'Manager', 'email' => 'manager@test.com',
            'password' => Hash::make('pass'), 'role' => 'Event Manager',
        ]);
        $this->admin = User::create([
            'name' => 'Admin', 'email' => 'admin@test.com',
            'password' => Hash::make('pass'), 'role' => 'Admin',
        ]);
    }

    protected function tearDown(): void
    {
        $this->tearDownEventLifecycleSchema();
        parent::tearDown();
    }

    public function test_manager_creates_event_successfully(): void
    {
        $response = $this->controller->store($this->eventRequest());
        $event = Event::findOrFail($response->getData(true)['id']);

        $this->assertSame(201, $response->getStatusCode());
        $this->assertSame('pending', $event->status);
        $this->assertFalse($event->is_published);
        $this->assertSame($this->manager->id, $event->created_by);
        $this->assertNotNull($event->schedule);
        $this->assertNotNull($event->externalVenue);
    }

    public function test_rejects_booking_less_than_60_days(): void
    {
        $response = $this->controller->store(
            $this->eventRequest(now()->addDays(30)->format('Y-m-d'))
        );

        $this->assertSame(422, $response->getStatusCode());
        $this->assertStringContainsString('60 days', $response->getData(true)['message']);
        $this->assertSame(0, Event::count());
    }

    public function test_submitted_event_stays_pending_awaiting_admin(): void
    {
        $event = $this->pendingEvent();

        $this->assertSame('pending', $event->status);
        $this->assertFalse($event->is_published);
        $this->assertFalse($event->fresh()->is_published);
    }

    public function test_notifies_admin_on_event_submission(): void
    {
        $this->controller->store($this->eventRequest());

        Notification::assertSentTo($this->admin, SystemNotification::class);
    }

    public function test_admin_lists_pending_events(): void
    {
        $event = $this->pendingEvent();

        $response = $this->controller->pending($this->as($this->admin));
        $ids = collect($response->getData(true))->pluck('id');

        $this->assertSame(200, $response->getStatusCode());
        $this->assertTrue($ids->contains($event->id));
    }

    public function test_admin_approves_pending_event(): void
    {
        $event = $this->pendingEvent();

        $response = $this->controller->approve($event->id, $this->as($this->admin));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('approved', $event->fresh()->status);
        $this->assertFalse($event->fresh()->is_published);
        Notification::assertSentTo($this->manager, SystemNotification::class);
    }

    public function test_admin_rejects_pending_event_with_reason(): void
    {
        $event = $this->pendingEvent();

        $response = $this->controller->reject(
            $event->id,
            $this->as($this->admin, Request::create('/', 'PUT', ['rejection_reason' => 'Incomplete documents']))
        );

        $event->refresh();
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('rejected', $event->status);
        $this->assertSame('Incomplete documents', $event->review->rejection_reason);
        Notification::assertSentTo($this->manager, SystemNotification::class);
    }

    public function test_manager_publishes_approved_event(): void
    {
        $event = $this->approvedEvent();
        $date = now()->addDays(70)->format('Y-m-d');

        $response = $this->controller->updatePublishedSchedule(
            $this->as($this->manager, Request::create("/api/events/{$event->id}/published-schedule", 'PUT', [
                'published_schedule' => [['date' => $date, 'start_time' => '09:00', 'end_time' => '17:00']],
                'publish' => true,
            ])),
            $event->id
        );

        $event->refresh();
        $this->assertSame(200, $response->getStatusCode());
        $this->assertTrue($event->is_published);
        $this->assertNotEmpty($event->schedule->published_schedule);
    }

    public function test_published_event_is_publicly_visible(): void
    {
        $event = $this->approvedEvent();
        $date = now()->addDays(70)->format('Y-m-d');

        $this->controller->updatePublishedSchedule(
            $this->as($this->manager, Request::create("/api/events/{$event->id}/published-schedule", 'PUT', [
                'published_schedule' => [['date' => $date, 'start_time' => '09:00', 'end_time' => '17:00']],
                'publish' => true,
            ])),
            $event->id
        );

        $this->assertTrue(
            Event::where('id', $event->id)
                ->where('status', 'approved')
                ->where('is_published', true)
                ->where(function ($q) {
                    $q->where('is_tickets_open', true)->orWhere('is_exhibitor_registration_open', true);
                })
                ->exists()
        );
    }

    private function pendingEvent(): Event
    {
        $id = $this->controller->store($this->eventRequest())->getData(true)['id'];
        return Event::findOrFail($id);
    }

    private function approvedEvent(): Event
    {
        $event = $this->pendingEvent();
        $this->controller->approve($event->id, $this->as($this->admin));
        return $event->fresh();
    }

    private function eventRequest(?string $date = null): Request
    {
        $date ??= now()->addDays(70)->format('Y-m-d');
        $schedule = json_encode([['date' => $date, 'start_time' => '09:00', 'end_time' => '17:00']]);
        $agenda = json_encode([$date => [['title' => 'Opening', 'start_time' => '09:00', 'end_time' => '10:00']]]);

        return $this->as($this->manager, Request::create('/api/events', 'POST', [
            'title' => 'Tech Conference', 'description' => 'Annual tech event',
            'event_type' => 'مؤتمر', 'location_type' => 'external', 'capacity' => 200,
            'external_venue_name' => 'Grand Hall', 'external_schedule' => $schedule,
            'agenda' => $agenda, 'event_objective' => 'Knowledge sharing', 'target_audience' => 'Developers',
        ], [], [
            'image' => UploadedFile::fake()->image('event.jpg'),
            'ministry_document' => UploadedFile::fake()->create('ministry.pdf', 1, 'application/pdf'),
            'booking_proof' => UploadedFile::fake()->create('proof.pdf', 1, 'application/pdf'),
        ]));
    }

    private function as(User $user, ?Request $request = null): Request
    {
        $request ??= Request::create('/');
        $request->setUserResolver(fn () => $user);
        return $request;
    }
}
