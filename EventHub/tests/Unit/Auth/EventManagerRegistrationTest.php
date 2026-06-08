<?php

namespace Tests\Unit\Auth;

use App\Http\Controllers\AuthController;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\Concerns\CreatesPartnerRegistrationSchema;
use Tests\TestCase;

class EventManagerRegistrationTest extends TestCase
{
    use CreatesPartnerRegistrationSchema;

    private AuthController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpPartnerRegistrationSchema();
        Storage::fake('local');
        Notification::fake();
        $this->controller = new AuthController(Mockery::mock(OtpService::class));
    }

    protected function tearDown(): void
    {
        $this->tearDownPartnerRegistrationSchema();
        Mockery::close();
        parent::tearDown();
    }
    public function test_registers_event_manager_successfully(): void
    {
        $response = $this->register();
        $user = User::where('email', 'manager@test.com')->firstOrFail();
        $payload = $response->getData(true);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('Event Manager', $user->role);
        $this->assertSame('pending', $user->verification_status);
        $this->assertTrue(Hash::check('password123', $user->password));
        $this->assertNull($user->profile);
        $this->assertCount(4, $user->documents);
        $this->assertEqualsCanonicalizing(
            ['commercial_register', 'tax_number', 'articles_of_association', 'practice_license'],
            $user->documents->pluck('document_type')->all()
        );
        $this->assertSame('manager@test.com', $payload['user']['email']);
        $this->assertStringContainsString('pending verification', $payload['message']);
    }
    public function test_rejects_missing_manager_documents(): void
    {
        $this->expectException(ValidationException::class);
        $this->register(withManagerDocs: false);
    }

    public function test_rejects_duplicate_email(): void
    {
        User::create([
            'name' => 'Existing', 'email' => 'manager@test.com',
            'password' => 'x', 'role' => 'Attendee',
        ]);
        $this->expectException(ValidationException::class);
        $this->register();
    }

    private function register(bool $withManagerDocs = true)
    {
        $files = [
            'doc_commercial_register' => UploadedFile::fake()->create('c.pdf', 1, 'application/pdf'),
            'doc_tax_number' => UploadedFile::fake()->create('t.pdf', 1, 'application/pdf'),
        ];
        if ($withManagerDocs) {
            $files['doc_articles_of_association'] = UploadedFile::fake()->create('a.pdf', 1, 'application/pdf');
            $files['doc_practice_license'] = UploadedFile::fake()->create('l.pdf', 1, 'application/pdf');
        }
        return $this->controller->registerPartner(Request::create(
            '/api/register/partner', 'POST',
            ['name' => 'Test Manager', 'email' => 'manager@test.com', 'password' => 'password123', 'role' => 'Event Manager'],
            [], $files
        ));
    }
}
