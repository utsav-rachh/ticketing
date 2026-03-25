<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::withTrashed()->with('supervisor')->orderBy('role')->paginate(20);
        return view('admin.users.index', compact('users'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:255',
            'email'      => 'required|email|unique:users',
            'password'   => 'required|min:8',
            'role'       => 'required|in:admin,md,ciso,hr_head,it_lead,app_lead,it_l1,app_l1,admin_l1,employee',
            'department' => 'nullable|string',
            'reports_to' => 'nullable|exists:users,id',
            'phone'      => 'nullable|string',
        ]);
        $data['password'] = Hash::make($data['password']);
        $data['email_verified_at'] = now();
        User::create($data);
        return back()->with('success', 'User created.');
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:255',
            'role'       => 'required|in:admin,md,ciso,hr_head,it_lead,app_lead,it_l1,app_l1,admin_l1,employee',
            'department' => 'nullable|string',
            'reports_to' => 'nullable|exists:users,id',
            'phone'      => 'nullable|string',
            'is_active'  => 'boolean',
        ]);
        $user->update($data);
        return back()->with('success', 'User updated.');
    }

    public function destroy(User $user)
    {
        $user->update(['is_active' => false]);
        $user->delete();
        return back()->with('success', 'User deactivated.');
    }
}
