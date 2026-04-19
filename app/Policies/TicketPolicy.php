<?php
namespace App\Policies;

use App\Models\Ticket;
use App\Models\TicketExpense;
use App\Models\User;

class TicketPolicy
{
    public function viewAny(User $user): bool
    {
        return true; // visibility enforced via Ticket::scopeVisibleTo
    }

    public function view(User $user, Ticket $ticket): bool
    {
        if ($user->isAdmin() || $user->isITHead()) return true;
        if ($user->isTL()) {
            return $ticket->assigned_to === $user->id
                || $ticket->support_type === $user->assigned_support_type;
        }
        if ($user->isJunior()) return $ticket->assigned_to === $user->id;
        if ($user->isManagement()) return $ticket->created_by === $user->id;

        // employee: created by them, or in a branch they can see
        if ($ticket->created_by === $user->id) return true;
        return $ticket->branch_id && in_array($ticket->branch_id, $user->visibleBranchIds());
    }

    public function assign(User $user, Ticket $ticket): bool
    {
        return $user->canAssign();
    }

    public function updateStatus(User $user, Ticket $ticket): bool
    {
        if ($user->isAdmin()) return true;
        if (!$user->isResolver()) return false;
        if ($user->isITHead()) return true;
        if ($user->isTL()) return $ticket->support_type === $user->assigned_support_type;
        return $ticket->assigned_to === $user->id;
    }

    /** Hold is infrastructure-only. */
    public function hold(User $user, Ticket $ticket): bool
    {
        if ($ticket->support_type !== 'infrastructure') return false;
        return $this->updateStatus($user, $ticket);
    }

    /** Employees can add notes & attachments to their own tickets. */
    public function comment(User $user, Ticket $ticket): bool
    {
        if ($user->isAdmin() || $user->isResolver()) {
            return $this->view($user, $ticket);
        }
        return $ticket->created_by === $user->id;
    }

    /** Only the creator or resolver/admin can attach. */
    public function attach(User $user, Ticket $ticket): bool
    {
        return $this->comment($user, $ticket);
    }

    /** Application Support tickets do NOT get expenses. */
    public function addExpense(User $user, Ticket $ticket): bool
    {
        if ($ticket->support_type === 'application') return false;
        return $user->isResolver() || $user->isAdmin();
    }

    public function approveExpense(User $user, TicketExpense $expense): bool
    {
        return $user->canApproveExpenses();
    }

    public function exportPriority(User $user): bool
    {
        return $user->canExport();
    }

    public function toggleRedFlag(User $user, Ticket $ticket): bool
    {
        return $user->isAdmin() || $user->isITHead();
    }
}
