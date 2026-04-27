<?php
namespace App\Services;

use App\Models\Ticket;
use App\Models\TicketActivity;
use App\Models\User;

/**
 * Auto-routes new tickets to the junior resolver who owns the
 * matching (support_type, region) pair. Falls back to the support_type TL
 * and then to the IT Head if no one fits.
 */
class TicketAssignmentService
{
    public function assign(Ticket $ticket): ?User
    {
        $regionId = $ticket->branch?->region_id;

        // Prefer a junior whose assigned_regions pivot contains this ticket's region.
        // Falls back to the legacy single assigned_region_id, then to anyone who
        // has no region restriction. Tie-break by fewest open tickets.
        $junior = User::query()
            ->where('role', 'resolver')
            ->where('resolver_level', 'junior')
            ->where('is_active', true)
            ->where('assigned_support_type', $ticket->support_type)
            ->when($regionId, function ($q) use ($regionId) {
                $q->where(function ($w) use ($regionId) {
                    $w->whereHas('assignedRegions', fn ($r) => $r->where('regions.id', $regionId))
                      ->orWhere(function ($x) use ($regionId) {
                          $x->where('assigned_region_id', $regionId)
                            ->whereDoesntHave('assignedRegions');
                      })
                      ->orWhere(function ($x) {
                          $x->whereNull('assigned_region_id')
                            ->whereDoesntHave('assignedRegions');
                      });
                });
            })
            ->withCount(['assignedTickets as open_count' => function ($q) {
                $q->whereNotIn('status', ['resolved','closed']);
            }])
            ->orderBy('open_count')
            ->first();

        $target = $junior
            ?: User::query()
                ->where('role', 'resolver')
                ->where('resolver_level', 'tl')
                ->where('assigned_support_type', $ticket->support_type)
                ->where('is_active', true)
                ->first()
            ?: User::query()
                ->where('role', 'resolver')
                ->where('resolver_level', 'it_head')
                ->where('is_active', true)
                ->first();

        if (!$target) {
            return null;
        }

        $ticket->assigned_to = $target->id;
        $ticket->assigned_by = null; // auto-assignment
        $ticket->assigned_at = now();
        if ($ticket->status === 'open') {
            $ticket->status = 'assigned';
        }
        $ticket->save();

        TicketActivity::create([
            'ticket_id'   => $ticket->id,
            'user_id'     => $target->id,
            'action_type' => 'assigned',
            'description' => 'Auto-assigned to ' . $target->name . ($regionId ? ' (region match)' : ''),
            'new_value'   => $target->name,
        ]);

        return $target;
    }
}
