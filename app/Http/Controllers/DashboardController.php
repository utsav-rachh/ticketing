<?php
namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $base = Ticket::visibleTo($user);

        $stats = [
            'total'       => (clone $base)->count(),
            'open'        => (clone $base)->where('status','open')->count(),
            'in_progress' => (clone $base)->where('status','in_progress')->count(),
            'resolved'    => (clone $base)->where('status','resolved')->count(),
            'violated'    => (clone $base)->where('is_tat_violated',true)->whereNotIn('status',['resolved','closed'])->count(),
        ];

        $recentTickets = (clone $base)->with(['creator','category','assignee'])
            ->latest()->take(10)->get();

        return view('dashboard', compact('stats','recentTickets'));
    }
}
