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
        return User::where('role', 'resolver')->where('is_active', true)->get();
    }
}
