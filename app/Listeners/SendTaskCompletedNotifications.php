<?php

namespace App\Listeners;

use App\Events\TaskCompleted;
use App\Notifications\TaskCompletedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendTaskCompletedNotifications implements ShouldQueue
{
    public function handle(TaskCompleted $event): void
    {
        foreach ($event->task->users as $user) {
            $user->notify(new TaskCompletedNotification($event->task));
        }
    }
}