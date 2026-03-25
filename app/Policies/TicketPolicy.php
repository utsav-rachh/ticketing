<?php
namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;

class TicketPolicy
{
    public function view(User $user, Ticket $ticket): bool
    {
        if (in_array($user->role, ['admin','md'])) return true;
        if ($user->role === 'employee') return $ticket->created_by === $user->id;
        if ($user->role === 'ciso') return in_array($ticket->support_type, ['application','infrastructure']);
        if ($user->role === 'hr_head') return $ticket->support_type === 'admin';
        if (in_array($user->role, ['it_lead','it_l1'])) return $ticket->support_type === 'infrastructure';
        if (in_array($user->role, ['app_lead','app_l1'])) return $ticket->support_type === 'application';
        if ($user->role === 'admin_l1') return $ticket->support_type === 'admin';
        return false;
    }

    public function assign(User $user, Ticket $ticket): bool
    {
        return in_array($user->role, ['admin','md','ciso','hr_head','it_lead','app_lead']);
    }

    public function update(User $user, Ticket $ticket): bool
    {
        if (in_array($user->role, ['admin','md','ciso','hr_head','it_lead','app_lead'])) return true;
        if (str_ends_with($user->role, '_l1')) return $ticket->assigned_to === $user->id;
        return false;
    }
}
