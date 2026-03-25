<?php
namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $teamMembers = User::where('reports_to', $user->id)->where('is_active', true)->get();

        $teamStats = $teamMembers->map(function ($member) {
            return [
                'user'        => $member,
                'open'        => Ticket::where('assigned_to', $member->id)->whereNotIn('status', ['resolved','closed'])->count(),
                'resolved'    => Ticket::where('assigned_to', $member->id)->where('status', 'resolved')->count(),
                'violated'    => Ticket::where('assigned_to', $member->id)->where('is_tat_violated', true)->count(),
            ];
        });

        return view('team.index', compact('teamMembers','teamStats'));
    }

    public function memberTickets(Request $request, User $user)
    {
        $tickets = Ticket::where(function($q) use ($user) {
            $q->where('assigned_to', $user->id)->orWhere('created_by', $user->id);
        })->with(['category','subcategory'])->latest()->paginate(20);

        return view('team.member', compact('user','tickets'));
    }
}
