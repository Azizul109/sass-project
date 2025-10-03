<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Task Completed</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #4F46E5; color: white; padding: 20px; text-align: center; }
        .content { background: #f9f9f9; padding: 20px; border-radius: 5px; }
        .task-info { background: white; padding: 15px; border-left: 4px solid #4F46E5; margin: 15px 0; }
        .footer { text-align: center; margin-top: 20px; color: #666; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Task Completed! 🎉</h1>
        </div>
        
        <div class="content">
            <p>Hello <strong>{{ $user->name }}</strong>,</p>
            
            <p>The task you were assigned to has been marked as completed:</p>
            
            <div class="task-info">
                <h3>{{ $task->title }}</h3>
                <p><strong>Project:</strong> {{ $task->project->name }}</p>
                <p><strong>Description:</strong> {{ $task->description ?? 'No description provided' }}</p>
                <p><strong>Status:</strong> Completed</p>
                <p><strong>Completed On:</strong> {{ date('M j, Y \a\t g:i A') }}</p>
            </div>
            
            <p>Great work on completing this task!</p>
            
            <p>If you have any questions, please contact your project manager.</p>
        </div>
        
        <div class="footer">
            <p>This is an automated notification from your Project Management System.</p>
        </div>
    </div>
</body>
</html>