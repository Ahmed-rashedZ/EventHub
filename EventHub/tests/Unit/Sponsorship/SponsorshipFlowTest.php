<?php

namespace Tests\Unit\Sponsorship;

use App\Http\Controllers\AgreementController;
use App\Http\Controllers\SponsorshipController;
use App\Models\AgreementNegotiation;
use App\Models\AgreementVersion;
use App\Models\Event;
use App\Models\EventSponsor;
use App\Models\SponsorshipRequest;
use App\Models\User;
use App\Notifications\SystemNotification;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\CreatesSponsorshipSchema;
use Tests\TestCase;

class SponsorshipFlowTest extends TestCase
{
    use CreatesSponsorshipSchema;

    private SponsorshipController $sponsorship;
    private AgreementController $agreement;
    private User $manager;
    private User $sponsor;
    private Event $event;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpSponsorshipSchema();
        Storage::fake('public');
        Notification::fake();

        $this->sponsorship = new SponsorshipController();
        $this->agreement = new AgreementController();

        $this->manager = User::create([
            'name' => 'Manager', 'email' => 'manager@test.com',
            'password' => Hash::make('pass'), 'role' => 'Event Manager',
        ]);
        $this->sponsor = User::create([
            'name' => 'Sponsor Co', 'email' => 'sponsor@test.com',
            'password' => Hash::make('pass'), 'role' => 'Sponsor',
        ]);
        $this->sponsor->profile()->create(['profile_type' => 'company', 'is_available' => true]);

        $this->event = Event::create([
            'title' => 'Tech Summit', 'description' => 'Annual summit',
            'event_type' => 'مؤتمر', 'start_time' => now()->addDays(90),
            'end_time' => now()->addDays(91), 'capacity' => 500,
            'status' => 'approved', 'is_sponsorship_open' => true,
            'created_by' => $this->manager->id,
        ]);
    }

    protected function tearDown(): void
    {
        $this->tearDownSponsorshipSchema();
        parent::tearDown();
    }

    public function test_sponsor_submits_sponsorship_request(): void
    {
        $response = $this->sponsorship->store($this->as($this->sponsor, Request::create('/api/sponsorship', 'POST', [
            'event_id' => $this->event->id, 'message' => 'Interested in sponsoring',
        ])));

        $sreq = SponsorshipRequest::first();
        $this->assertSame(201, $response->getStatusCode());
        $this->assertSame('pending', $sreq->status);
        $this->assertSame('sponsor', $sreq->initiator);
        $this->assertSame($this->sponsor->id, $sreq->sponsor_id);
        Notification::assertSentTo($this->manager, SystemNotification::class);
    }

    public function test_manager_sends_sponsorship_invitation(): void
    {
        $response = $this->sponsorship->store($this->as($this->manager, Request::create('/api/sponsorship', 'POST', [
            'event_id' => $this->event->id, 'sponsor_id' => $this->sponsor->id, 'message' => 'Join us',
        ])));

        $sreq = SponsorshipRequest::first();
        $this->assertSame(201, $response->getStatusCode());
        $this->assertSame('pending', $sreq->status);
        $this->assertSame('event_manager', $sreq->initiator);
        Notification::assertSentTo($this->sponsor, SystemNotification::class);
    }

    public function test_rejects_sponsorship_for_unapproved_event(): void
    {
        $this->event->update(['status' => 'pending']);

        $response = $this->sponsorship->store($this->as($this->sponsor, Request::create('/api/sponsorship', 'POST', [
            'event_id' => $this->event->id,
        ])));

        $this->assertSame(400, $response->getStatusCode());
        $this->assertSame(0, SponsorshipRequest::count());
    }

    public function test_manager_accepts_request_and_starts_negotiation(): void
    {
        $sreq = $this->pendingSponsorRequest();
        $this->bypassWordGeneration($sreq);

        $response = $this->sponsorship->update(
            $this->as($this->manager, Request::create('/', 'PUT', ['status' => 'accepted'])),
            $sreq->id
        );

        $sreq->refresh();
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('negotiating', $sreq->status);
        Notification::assertSentTo($this->sponsor, SystemNotification::class);
    }

    public function test_acceptance_creates_negotiation_with_initial_version(): void
    {
        $sreq = $this->pendingSponsorRequest();
        $this->seedNegotiation($sreq, $this->manager);
        $sreq->update(['status' => 'negotiating']);

        $sreq->refresh();
        $this->assertSame('negotiating', $sreq->status);
        $this->assertSame('draft', $sreq->negotiation->status);
        $this->assertCount(1, $sreq->negotiation->versions);
        $this->assertSame('uploaded', $sreq->negotiation->versions->first()->action);
    }

    public function test_manager_rejects_sponsor_request(): void
    {
        $sreq = $this->pendingSponsorRequest();

        $this->sponsorship->update(
            $this->as($this->manager, Request::create('/', 'PUT', ['status' => 'rejected'])),
            $sreq->id
        );

        $this->assertSame('rejected', $sreq->fresh()->status);
        Notification::assertSentTo($this->sponsor, SystemNotification::class);
    }

    public function test_sponsor_accepts_manager_invitation(): void
    {
        $sreq = SponsorshipRequest::create([
            'event_id' => $this->event->id, 'sponsor_id' => $this->sponsor->id,
            'event_manager_id' => $this->manager->id, 'initiator' => 'event_manager',
            'message' => 'Invitation', 'status' => 'pending',
        ]);
        $this->bypassWordGeneration($sreq);

        $this->sponsorship->update(
            $this->as($this->sponsor, Request::create('/', 'PUT', ['status' => 'accepted'])),
            $sreq->id
        );

        $sreq->refresh();
        $this->assertSame('negotiating', $sreq->status);
        Notification::assertSentTo($this->manager, SystemNotification::class);
    }

    public function test_sponsor_uploads_revised_contract(): void
    {
        $sreq = $this->negotiatingRequest();

        $response = $this->agreement->upload(
            $this->as($this->sponsor, Request::create("/api/agreements/{$sreq->id}/upload?type=sponsor", 'POST', [], [], [
                'file' => UploadedFile::fake()->create('contract.pdf', 10, 'application/pdf'),
            ], ['CONTENT_TYPE' => 'multipart/form-data'])),
            $sreq->id
        );

        $negotiation = $sreq->fresh()->negotiation;
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('pending_review', $negotiation->status);
        $this->assertSame(2, $negotiation->versions()->count());
        Notification::assertSentTo($this->manager, SystemNotification::class);
    }

    public function test_manager_requests_contract_revision(): void
    {
        $sreq = $this->negotiatingRequest();
        $this->uploadContract($sreq, $this->sponsor);

        $response = $this->agreement->respond(
            $this->as($this->manager, Request::create("/api/agreements/{$sreq->id}/respond?type=sponsor", 'PUT', [
                'action' => 'revision_requested', 'message' => 'Please adjust clause 3',
            ])),
            $sreq->id
        );

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('revision_requested', $sreq->fresh()->negotiation->status);
        Notification::assertSentTo($this->sponsor, SystemNotification::class);
    }

    public function test_manager_accepts_final_contract(): void
    {
        $sreq = $this->negotiatingRequest();
        $this->uploadContract($sreq, $this->sponsor);

        $this->agreement->respond(
            $this->as($this->manager, Request::create("/api/agreements/{$sreq->id}/respond?type=sponsor", 'PUT', [
                'action' => 'accepted',
            ])),
            $sreq->id
        );

        $sreq->refresh();
        $this->assertSame('accepted', $sreq->status);
        $this->assertSame('accepted', $sreq->negotiation->status);
        $this->assertTrue(EventSponsor::where('event_id', $this->event->id)->where('sponsor_id', $this->sponsor->id)->exists());
        Notification::assertSentTo($this->sponsor, SystemNotification::class);
    }

    private function pendingSponsorRequest(): SponsorshipRequest
    {
        $this->sponsorship->store($this->as($this->sponsor, Request::create('/api/sponsorship', 'POST', [
            'event_id' => $this->event->id, 'message' => 'Sponsorship request',
        ])));
        return SponsorshipRequest::first();
    }

    private function negotiatingRequest(): SponsorshipRequest
    {
        $sreq = $this->pendingSponsorRequest();
        $this->seedNegotiation($sreq, $this->manager);
        $sreq->update(['status' => 'negotiating']);
        return $sreq->fresh();
    }

    private function bypassWordGeneration(SponsorshipRequest $sreq): void
    {
        AgreementNegotiation::create([
            'sponsorship_request_id' => $sreq->id,
            'status' => 'draft',
            'last_submitted_by' => $this->manager->id,
        ]);
    }

    private function seedNegotiation(SponsorshipRequest $sreq, User $submitter): void
    {
        Storage::disk('public')->put('agreements/initial.docx', 'contract');
        $negotiation = AgreementNegotiation::create([
            'sponsorship_request_id' => $sreq->id,
            'status' => 'draft',
            'last_submitted_by' => $submitter->id,
        ]);
        AgreementVersion::create([
            'negotiation_id' => $negotiation->id,
            'version_number' => 1,
            'file_path' => 'agreements/initial.docx',
            'uploaded_by' => $submitter->id,
            'action' => 'uploaded',
            'message' => 'تم توليد العقد الأولي تلقائياً',
        ]);
    }

    private function uploadContract(SponsorshipRequest $sreq, User $user): void
    {
        $this->agreement->upload(
            $this->as($user, Request::create("/api/agreements/{$sreq->id}/upload?type=sponsor", 'POST', [], [], [
                'file' => UploadedFile::fake()->create('contract.pdf', 10, 'application/pdf'),
            ])),
            $sreq->id
        );

        $path = $sreq->fresh()->negotiation->latestVersion->file_path;
        $full = storage_path('app/public/' . $path);
        if (! is_dir(dirname($full))) {
            mkdir(dirname($full), 0777, true);
        }
        file_put_contents($full, '%PDF-1.4');
    }

    private function as(User $user, ?Request $request = null): Request
    {
        $request ??= Request::create('/');
        $request->setUserResolver(fn () => $user);
        return $request;
    }
}
