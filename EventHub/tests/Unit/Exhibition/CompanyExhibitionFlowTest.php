<?php

namespace Tests\Unit\Exhibition;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompanyAnalyticsController;
use App\Http\Controllers\ExhibitionController;
use App\Models\Event;
use App\Models\ExhibitionApplication;
use App\Models\User;
use App\Notifications\SystemNotification;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Mockery;
use Tests\Concerns\CreatesCompanyExhibitionSchema;
use Tests\TestCase;

class CompanyExhibitionFlowTest extends TestCase
{
    use CreatesCompanyExhibitionSchema;

    private ExhibitionController $exhibition;
    private CompanyAnalyticsController $companyAnalytics;
    private AuthController $auth;
    private User $manager;
    private User $techCompany;
    private User $foodCompany;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpCompanyExhibitionSchema();
        Notification::fake();

        $this->exhibition = new ExhibitionController();
        $this->companyAnalytics = new CompanyAnalyticsController();
        $this->auth = new AuthController(Mockery::mock(OtpService::class));

        $this->manager = User::create([
            'name' => 'Manager', 'email' => 'manager@test.com',
            'password' => Hash::make('pass'), 'role' => 'Event Manager',
        ]);
        $this->techCompany = $this->makeCompany('Tech Co', 'tech@test.com', 'tech', 'تقنية');
        $this->foodCompany = $this->makeCompany('Food Co', 'food@test.com', 'food', 'غذاء');
    }

    protected function tearDown(): void
    {
        $this->tearDownCompanyExhibitionSchema();
        Mockery::close();
        parent::tearDown();
    }

    public function test_company_browse_shows_only_matching_category_exhibitions(): void
    {
        $this->exhibitionEvent(['title' => 'Tech Expo', 'company_category_slug' => 'tech']);
        $this->exhibitionEvent(['title' => 'Food Expo', 'company_category_slug' => 'food']);

        $ids = collect($this->companyAnalytics->browseExhibitions($this->as($this->techCompany))
            ->getData(true))->pluck('id');

        $this->assertCount(1, $ids);
        $this->assertSame('Tech Expo', Event::find($ids->first())->title);
    }

    public function test_company_browse_excludes_non_exhibition_events(): void
    {
        $this->exhibitionEvent(['title' => 'Tech Expo', 'company_category_slug' => 'tech']);
        Event::create([
            'title' => 'Tech Conference', 'description' => 'Not exhibition',
            'event_type' => 'مؤتمر', 'is_exhibition' => false,
            'company_category_slug' => 'tech', 'start_time' => now()->addDays(90),
            'end_time' => now()->addDays(91), 'status' => 'approved',
            'is_published' => true, 'is_exhibitor_registration_open' => true,
            'created_by' => $this->manager->id,
        ]);

        $titles = collect($this->companyAnalytics->browseExhibitions($this->as($this->techCompany))
            ->getData(true))->pluck('title');

        $this->assertSame(['Tech Expo'], $titles->all());
    }

    public function test_company_submits_exhibition_application(): void
    {
        $event = $this->exhibitionEvent(['company_category_slug' => 'tech']);

        $response = $this->exhibition->store($this->as($this->techCompany, Request::create('/api/exhibition', 'POST', [
            'event_id' => $event->id, 'message' => 'We want a booth', 'product_category' => 'Hardware',
        ])));

        $app = ExhibitionApplication::first();
        $this->assertSame(201, $response->getStatusCode());
        $this->assertSame('pending', $app->status);
        $this->assertSame('company', $app->initiator);
        Notification::assertSentTo($this->manager, SystemNotification::class);
    }

    public function test_company_cannot_apply_to_non_exhibition_event(): void
    {
        $event = Event::create([
            'title' => 'Conference', 'description' => 'Regular event',
            'event_type' => 'مؤتمر', 'is_exhibition' => false,
            'start_time' => now()->addDays(90), 'end_time' => now()->addDays(91),
            'status' => 'approved', 'created_by' => $this->manager->id,
        ]);

        $response = $this->exhibition->store($this->as($this->techCompany, Request::create('/api/exhibition', 'POST', [
            'event_id' => $event->id,
        ])));

        $this->assertSame(400, $response->getStatusCode());
        $this->assertSame(0, ExhibitionApplication::count());
    }

    public function test_company_cannot_apply_to_different_category_exhibition(): void
    {
        $event = $this->exhibitionEvent(['company_category_slug' => 'food']);

        $response = $this->exhibition->store($this->as($this->techCompany, Request::create('/api/exhibition', 'POST', [
            'event_id' => $event->id,
        ])));

        $this->assertSame(403, $response->getStatusCode());
        $this->assertSame(0, ExhibitionApplication::count());
    }

    public function test_manager_sends_exhibition_invitation_to_matching_company(): void
    {
        $event = $this->exhibitionEvent(['company_category_slug' => 'tech']);

        $response = $this->exhibition->store($this->as($this->manager, Request::create('/api/exhibition', 'POST', [
            'event_id' => $event->id, 'company_id' => $this->techCompany->id, 'message' => 'Join our expo',
        ])));

        $app = ExhibitionApplication::first();
        $this->assertSame(201, $response->getStatusCode());
        $this->assertSame('event_manager', $app->initiator);
        Notification::assertSentTo($this->techCompany, SystemNotification::class);
    }

    public function test_manager_cannot_invite_company_with_wrong_category(): void
    {
        $event = $this->exhibitionEvent(['company_category_slug' => 'tech']);

        $response = $this->exhibition->store($this->as($this->manager, Request::create('/api/exhibition', 'POST', [
            'event_id' => $event->id, 'company_id' => $this->foodCompany->id,
        ])));

        $this->assertSame(400, $response->getStatusCode());
        $this->assertSame(0, ExhibitionApplication::count());
    }

    public function test_available_companies_filtered_by_exhibition_category(): void
    {
        $event = $this->exhibitionEvent(['company_category_slug' => 'tech']);

        $ids = collect($this->auth->getAvailableCompanies($this->as($this->manager, Request::create(
            '/api/companies/available', 'GET', ['event_id' => $event->id]
        )))->getData(true))->pluck('id');

        $this->assertTrue($ids->contains($this->techCompany->id));
        $this->assertFalse($ids->contains($this->foodCompany->id));
    }

    private function exhibitionEvent(array $overrides = []): Event
    {
        return Event::create(array_merge([
            'title' => 'Expo', 'description' => 'Exhibition event',
            'event_type' => 'معرض', 'is_exhibition' => true,
            'company_category_slug' => 'tech',
            'start_time' => now()->addDays(90),
            'end_time' => now()->addDays(91),
            'status' => 'approved', 'is_published' => true,
            'is_exhibitor_registration_open' => true,
            'created_by' => $this->manager->id,
        ], $overrides));
    }

    private function makeCompany(string $name, string $email, string $slug, string $label): User
    {
        $user = User::create([
            'name' => $name, 'email' => $email,
            'password' => Hash::make('pass'), 'role' => 'Company',
            'verification_status' => 'verified',
        ]);
        $user->profile()->create([
            'profile_type' => 'company', 'company_type' => $label,
            'company_type_slug' => $slug, 'is_available' => true,
        ]);
        return $user;
    }

    private function as(User $user, ?Request $request = null): Request
    {
        $request ??= Request::create('/');
        $request->setUserResolver(fn () => $user);
        return $request;
    }
}
