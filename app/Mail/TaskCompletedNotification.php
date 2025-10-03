<?php

namespace App\Mail;

use App\Models\Task;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class TaskCompletedNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Task $task, public User $user)
    {
        Log::info("TaskCompletedNotification constructor called for: " . $user->email);
    }

    public function envelope(): Envelope
    {
        Log::info("Building envelope for: " . $this->user->email);

        return new Envelope(
            subject: 'Task Completed: ' . $this->task->title,
        );
    }

    public function content(): Content
    {
        Log::info("Building content for: " . $this->user->email);

        return new Content(
            view: 'emails.task-completed', // Use the simple template
            with: [
                'task' => $this->task,
                'user' => $this->user,
                'project' => $this->task->project,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        Log::info("Build method called for: " . $this->user->email);

        return $this->view('emails.task-completed') // Use the simple template
            ->with([
                'task' => $this->task,
                'user' => $this->user,
                'project' => $this->task->project,
            ]);
    }
}