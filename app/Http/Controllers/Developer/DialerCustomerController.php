<?php
namespace App\Http\Controllers\Developer;

use App\Http\Controllers\Controller;
use App\Jobs\Dialer\ImportDialerCustomers;
use App\Models\CsvImport;
use App\Models\DialerCustomer;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Dialer customer database — manual add + CSV bulk import with phone-number
 * deduplication. Developer-only.
 */
class DialerCustomerController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->get('q'));

        $customers = DialerCustomer::query()
            ->when($q !== '', function ($query) use ($q) {
                $digits = DialerCustomer::normalizePhone($q);
                $query->where(function ($w) use ($q, $digits) {
                    $w->where('name', 'like', "%{$q}%")
                      ->orWhere('company', 'like', "%{$q}%")
                      ->orWhere('email', 'like', "%{$q}%");
                    if ($digits !== '') $w->orWhere('phone', 'like', "%{$digits}%");
                });
            })
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('developer.dialer.customers.index', [
            'customers'   => $customers,
            'q'           => $q,
            'recentImports' => CsvImport::latest()->limit(5)->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'    => ['required', 'string', 'max:255'],
            'phone'   => ['required', 'string', 'max:32'],
            'email'   => ['nullable', 'email', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'notes'   => ['nullable', 'string', 'max:2000'],
        ]);

        $phone = DialerCustomer::normalizePhone($data['phone']);
        if ($phone === '') {
            return back()->with('error', 'That phone number doesn’t contain any digits.');
        }
        if (DialerCustomer::where('phone', $phone)->exists()) {
            return back()->with('error', "A customer with phone {$phone} already exists.");
        }

        DialerCustomer::create([
            'name'          => $data['name'],
            'phone'         => $phone,
            'email'         => $data['email'] ?? null,
            'company'       => $data['company'] ?? null,
            'notes'         => $data['notes'] ?? null,
            'imported_from' => 'manual',
            'created_by'    => $request->user()->id,
        ]);

        return back()->with('success', 'Customer added.');
    }

    public function update(Request $request, DialerCustomer $dialerCustomer)
    {
        $data = $request->validate([
            'name'    => ['required', 'string', 'max:255'],
            'email'   => ['nullable', 'email', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'notes'   => ['nullable', 'string', 'max:2000'],
        ]);
        // Phone is the dedup key — not editable here on purpose.
        $dialerCustomer->update($data);

        return back()->with('success', 'Customer updated.');
    }

    public function importForm()
    {
        return view('developer.dialer.customers.import', [
            'imports' => CsvImport::with('importer')->latest()->limit(25)->get(),
        ]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:10240'],
        ]);

        $file     = $request->file('file');
        $original = $file->getClientOriginalName();
        $stored   = $file->storeAs('dialer-imports', Str::uuid().'.csv');

        $import = CsvImport::create([
            'filename'    => $original,
            'status'      => 'processing',
            'imported_by' => $request->user()->id,
        ]);

        ImportDialerCustomers::dispatch($import->id, $stored, $request->user()->id);

        return redirect()->route('developer.dialer.customers.import')
            ->with('success', "Importing “{$original}” — refresh in a moment for the result.");
    }
}
