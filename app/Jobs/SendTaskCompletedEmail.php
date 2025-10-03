<?php

namespace App\Jobs;

use App\Mail\TaskCompletedNotification;
use App\Models\Task;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendTaskCompletedEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 60;

    public function __construct(public User $user, public Task $task)
    {
        // This job will be queued automatically
    }

    public function handle(): void
    {
        try {
            Mail::to($this->user->email)
                ->send(new TaskCompletedNotification($this->task, $this->user));
            
            \Log::info("Task completion email sent to {$this->user->email} for task {$this->task->id}");
        } catch (\Exception $e) {
            \Log::error("Failed to send task completion email to {$this->user->email}: " . $e->getMessage());
            $this->fail($e);
        }
    }

    public function retryUntil()
    {
        return now()->addMinutes(10);
    }
}