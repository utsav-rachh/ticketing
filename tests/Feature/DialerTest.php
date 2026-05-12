<?php

namespace Tests\Feature;

use App\Jobs\Dialer\ImportDialerCustomers;
use App\Models\CsvImport;
use App\Models\DialerCustomer;
use App\Models\DialerTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DialerTest extends TestCase
{
    use RefreshDatabase;

    private function developer(): User
    {
        return User::create([
            'name' => 'Dev', 'email' => 'dev_'.uniqid().'@altumcredo.test',
            'password' => bcrypt('password'), 'role' => 'developer',
            'is_active' => true, 'email_verified_at' => now(),
        ]);
    }

    public function test_phone_normalisation_collapses_indian_formats(): void
    {
        $this->assertSame('9876543210', DialerCustomer::normalizePhone('+91 98765-43210'));
        $this->assertSame('9876543210', DialerCustomer::normalizePhone('09876543210'));
        $this->assertSame('9876543210', DialerCustomer::normalizePhone('98765 43210'));
    }

    public function test_manual_add_rejects_duplicate_phone(): void
    {
        $dev = $this->developer();
        DialerCustomer::create(['name' => 'A', 'phone' => '9876543210', 'imported_from' => 'manual']);

        $this->actingAs($dev)->post(route('developer.dialer.customers.store'), [
            'name' => 'B', 'phone' => '+91 98765 43210',
        ])->assertRedirect();

        $this->assertSame(1, DialerCustomer::count());
    }

    public function test_csv_import_dedups_within_file_and_against_db(): void
    {
        Storage::fake();
        DialerCustomer::create(['name' => 'Existing', 'phone' => '9000000001', 'imported_from' => 'manual']);

        $csv = "name,phone,company\n"
             . "Alice,9000000001,Acme\n"        // dup of existing
             . "Bob,9000000002,Acme\n"
             . "Bob again,+91 90000 00002,Acme\n" // dup within file
             . "Carol,,Acme\n"                   // no phone -> failed
             . "Dave,9000000003,Acme\n";
        $path = 'dialer-imports/test.csv';
        Storage::put($path, $csv);

        $import = CsvImport::create(['filename' => 'test.csv', 'status' => 'processing']);
        (new ImportDialerCustomers($import->id, $path))->handle();

        $import->refresh();
        $this->assertSame('completed', $import->status);
        $this->assertSame(5, $import->total_rows);
        $this->assertSame(2, $import->imported);      // Bob, Dave
        $this->assertSame(2, $import->duplicates);    // Alice, Bob again
        $this->assertSame(1, $import->failed);        // Carol
        $this->assertSame(3, DialerCustomer::count());
    }

    public function test_click_to_call_creates_ticket_but_fails_gracefully_without_smartping(): void
    {
        config(['services.smartping.api_key' => null]);
        $dev = $this->developer();
        $cust = DialerCustomer::create(['name' => 'Cust', 'phone' => '9123456780', 'imported_from' => 'manual']);

        $this->actingAs($dev)->post(route('developer.dialer.call'), [
            'agent_number' => '9999999999',
            'customer_id'  => $cust->id,
        ])->assertRedirect();

        $ticket = DialerTicket::first();
        $this->assertNotNull($ticket);
        $this->assertSame('outbound', $ticket->direction);
        $this->assertSame('failed', $ticket->call_status);   // Smartping unconfigured
        $this->assertSame($dev->id, $ticket->agent_id);
        $this->assertStringStartsWith('DLR-', $ticket->ticket_number);
    }

    public function test_webhook_endpoint_accepts_and_logs(): void
    {
        $this->postJson('/api/smartping/missed', [
            'sessionId' => 'sess-1', 'customerNumber' => '9123456780', 'direction' => 'inbound',
        ])->assertOk();

        // raw payload logged + a missed ticket created by the queued job
        // (queue runs sync in tests)
        $this->assertDatabaseHas('dialer_call_logs', ['event' => 'webhook:missed']);
        $this->assertDatabaseHas('dialer_tickets', ['call_status' => 'missed', 'customer_phone' => '9123456780']);
    }
}
