<?php
namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class TeamController extends Controller
{
    /**
     * Hierarchy view of the resolver org. Visibility:
     *   - Admin / IT Head: see the full tree (Application + Infrastructure groups)
     *   - TL: their own support_type group only (themselves + their juniors)
     *   - Junior: just themselves
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $resolvers = $this->resolversVisibleTo($user);
        $statsById = $this->ticketStatsFor($resolvers->pluck('id')->all());

        $groups = [];
        foreach (['application' => 'Application', 'infrastructure' => 'Infrastructure'] as $type => $label) {
            $tl = $resolvers->first(
                fn ($u) => $u->resolver_level === 'tl' && $u->assigned_support_type === $type
            );
            $juniors = $resolvers
                ->where('resolver_level', 'junior')
                ->where('assigned_support_type', $type)
                ->values();

            // Skip empty groups so a junior doesn't see an empty Infra block on their own page.
            if (!$tl && $juniors->isEmpty()) continue;
            $groups[] = compact('type', 'label', 'tl', 'juniors');
        }

        $itHead = $resolvers->first(fn ($u) => $u->resolver_level === 'it_head');

        return view('team.index', compact('groups', 'itHead', 'statsById'));
    }

    public function memberTickets(Request $request, User $user)
    {
        $tickets = Ticket::where(function($q) use ($user) {
            $q->where('assigned_to', $user->id)->orWhere('created_by', $user->id);
        })->with(['category','subcategory'])->latest()->paginate(20);

        return view('team.member', compact('user','tickets'));
    }

    /** Resolvers visible to the current user under the hierarchy rules. */
    private function resolversVisibleTo(User $user): Collection
    {
        $base = User::where('role', 'resolver')->where('is_active', true);

        if ($user->isAdmin() || $user->isITHead()) {
            return $base->orderBy('resolver_level')->orderBy('name')->get();
        }
        if ($user->isTL()) {
            return $base
                ->where('assigned_support_type', $user->assigned_support_type)
                ->orderBy('resolver_level')->orderBy('name')->get();
        }
        if ($user->isJunior()) {
            return User::whereKey($user->id)->get();
        }
        return collect();
    }

    /** Aggregate ticket counts per resolver in one query. */
    private function ticketStatsFor(array $userIds): array
    {
        if (empty($userIds)) return [];

        $stats = Ticket::query()
            ->selectRaw('assigned_to as user_id')
            ->selectRaw("SUM(CASE WHEN status NOT IN ('resolved','closed') THEN 1 ELSE 0 END) as open_count")
            ->selectRaw("SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved_count")
            ->selectRaw('SUM(is_tat_violated) as violated_count')
            ->selectRaw('COUNT(*) as total_count')
            ->whereIn('assigned_to', $userIds)
            ->groupBy('assigned_to')
            ->get();

        $byId = [];
        foreach ($stats as $row) {
            $byId[$row->user_id] = [
                'open'     => (int) $row->open_count,
                'resolved' => (int) $row->resolved_count,
                'violated' => (int) $row->violated_count,
                'total'    => (int) $row->total_count,
            ];
        }
        return $byId;
    }
}
