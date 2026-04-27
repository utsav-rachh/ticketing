<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use App\Models\VendorAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VendorController extends Controller
{
    private const ATTACHMENT_MAX_KB = 15360; // 15 MB per file

    public function index()
    {
        $vendors = Vendor::orderBy('name')->paginate(25);
        return view('admin.vendors.index', compact('vendors'));
    }

    public function create() { return view('admin.vendors.edit', ['vendor' => new Vendor()]); }

    public function store(Request $request)
    {
        $vendor = Vendor::create($this->validated($request));
        $this->saveAttachments($request, $vendor);
        return redirect()->route('admin.vendors.edit', $vendor)->with('success', 'Vendor created.');
    }

    public function edit(Vendor $vendor)
    {
        $vendor->load('attachments.uploader');
        return view('admin.vendors.edit', compact('vendor'));
    }

    public function update(Request $request, Vendor $vendor)
    {
        $vendor->update($this->validated($request, $vendor->id));
        $this->saveAttachments($request, $vendor);
        return redirect()->route('admin.vendors.edit', $vendor)->with('success', 'Vendor updated.');
    }

    public function destroy(Vendor $vendor)
    {
        $vendor->update(['is_active' => false]);
        $vendor->delete();
        return redirect()->route('admin.vendors.index')->with('success', 'Vendor deleted.');
    }

    public function destroyAttachment(Vendor $vendor, VendorAttachment $attachment)
    {
        abort_unless($attachment->vendor_id === $vendor->id, 404);
        if ($attachment->file_path) {
            Storage::disk('public')->delete($attachment->file_path);
        }
        $attachment->delete();
        return back()->with('success', 'Attachment removed.');
    }

    private function validated(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'vendor_code'    => 'nullable|string|max:30|unique:vendors,vendor_code' . ($id ? ",{$id}" : ''),
            'name'           => 'required|string|max:200',
            'contact_person' => 'nullable|string|max:150',
            'phone'          => 'nullable|string|max:30',
            'email'          => 'nullable|email|max:150',
            'address'        => 'nullable|string|max:500',
            'notes'          => 'nullable|string',
            'is_active'      => 'nullable|boolean',
            'attachments'    => 'nullable|array',
            'attachments.*'  => 'file|max:' . self::ATTACHMENT_MAX_KB,
            'attachment_comment' => 'nullable|string|max:500',
        ]);
    }

    private function saveAttachments(Request $request, Vendor $vendor): void
    {
        if (!$request->hasFile('attachments')) return;

        $comment = $request->input('attachment_comment');
        foreach ((array) $request->file('attachments') as $file) {
            if (!$file) continue;
            $path = $file->store('vendor-docs/' . $vendor->id, 'public');
            VendorAttachment::create([
                'vendor_id'   => $vendor->id,
                'uploaded_by' => $request->user()?->id,
                'file_name'   => $file->getClientOriginalName(),
                'file_path'   => $path,
                'file_size'   => $file->getSize(),
                'mime_type'   => $file->getMimeType(),
                'comment'     => $comment,
                'created_at'  => now(),
            ]);
        }
    }
}
