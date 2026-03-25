<?php
namespace App\Jobs;

use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TATBreachedNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class SendTATNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Ticket $ticket) {}

    public function handle(): void
    {
        foreach ($this->getRecipients() as $user) {
            $user->notify(new TATBreachedNotification($this->ticket));
        }
    }

    private function getRecipients(): Collection
    {
        $leadRole = match($this->ticket->support_type) {
            'infrastructure' => 'it_lead',
            'application'    => 'app_lead',
            'admin'          => 'hr_head',
        };

        $users = User::where('role', $leadRole)->get();

        if (in_array($this->ticket->support_type, ['infrastructure','application'])) {
            $users = $users->merge(User::where('role','ciso')->get());
        }

        if ($this->ticket->priority === 'critical') {
            $users = $users->merge(User::where('role','md')->get());
        }

        return $users->unique('id');
    }
}
