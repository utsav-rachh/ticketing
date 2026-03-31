<?php
namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;

class TicketPolicy
{
    public function view(User $user, Ticket $ticket): bool
    {
        if ($user->role === 'resolver') return true;
        return $ticket->created_by === $user->id;
    }

    public function assign(User $user, Ticket $ticket): bool
    {
        return $user->role === 'resolver';
    }

    public function update(User $user, Ticket $ticket): bool
    {
        if ($user->role === 'resolver') return true;
        return $ticket->assigned_to === $user->id;
    }
}
