<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Region;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users    = User::withTrashed()->with(['supervisor','branch','region','assignedRegion','assignedRegions'])
            ->orderBy('role')->orderBy('resolver_level')->paginate(25);
        $regions  = Region::active()->orderBy('name')->get();
        $branches = Branch::active()->orderBy('name')->get();
        return view('admin.users.index', compact('users','regions','branches'));
    }

    public function create()
    {
        $regions  = Region::active()->orderBy('name')->get();
        $branches = Branch::active()->orderBy('name')->get();
        return view('admin.users.create', compact('regions','branches'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request, true);
        $regionIds = $this->extractRegionIds($data);
        $data['password']          = Hash::make($data['password']);
        $data['email_verified_at'] = now();
        $data['assigned_region_id'] = $regionIds[0] ?? null; // keep legacy single-region in sync

        $user = User::create($data);
        $user->assignedRegions()->sync($regionIds);
        return redirect()->route('admin.users.index')->with('success', 'User created.');
    }

    public function edit(User $user)
    {
        $user->load('assignedRegions');
        $regions  = Region::active()->orderBy('name')->get();
        $branches = Branch::active()->orderBy('name')->get();
        return view('admin.users.edit', compact('user','regions','branches'));
    }

    public function update(Request $request, User $user)
    {
        $data = $this->validated($request, false, $user->id);
        $regionIds = $this->extractRegionIds($data);
        if (empty($data['password'])) unset($data['password']);
        else $data['password'] = Hash::make($data['password']);
        $data['assigned_region_id'] = $regionIds[0] ?? null;

        $user->update($data);
        $user->assignedRegions()->sync($regionIds);
        return redirect()->route('admin.users.index')->with('success', 'User updated.');
    }

    public function destroy(User $user)
    {
        $user->update(['is_active' => false]);
        $user->delete();
        return back()->with('success', 'User deactivated.');
    }

    private function validated(Request $request, bool $creating, ?int $id = null): array
    {
        return $request->validate([
            'name'                  => 'required|string|max:255',
            'email'                 => 'required|email|unique:users,email' . ($id ? ",{$id}" : ''),
            'password'              => $creating ? 'required|min:6' : 'nullable|min:6',
            'role'                  => 'required|in:employee,resolver,admin,management',
            'resolver_level'        => 'nullable|in:junior,tl,it_head',
            'department'            => 'nullable|string|max:100',
            'reports_to'            => 'nullable|exists:users,id',
            'phone'                 => 'nullable|string|max:20',
            'employee_id'           => 'nullable|string|max:50',
            'branch_id'             => 'nullable|exists:branches,id',
            'region_id'             => 'nullable|exists:regions,id',
            'assigned_region_ids'   => 'nullable|array',
            'assigned_region_ids.*' => 'integer|exists:regions,id',
            'assigned_support_type' => 'nullable|in:application,infrastructure,admin',
            'is_active'             => 'nullable|boolean',
        ]);
    }

    private function extractRegionIds(array &$data): array
    {
        $ids = array_values(array_unique(array_map('intval', $data['assigned_region_ids'] ?? [])));
        unset($data['assigned_region_ids']);
        return $ids;
    }
}
