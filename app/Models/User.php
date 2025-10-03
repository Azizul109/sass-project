<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'company_id',
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class)->withDefault(function ($company, $user) {
            // Provide a default company if none exists
            $defaultCompany = Company::first();
            if ($defaultCompany) {
                // Auto-assign user to first available company
                $user->company_id = $defaultCompany->id;
                $user->save();
                return $defaultCompany;
            }
            
            // Create a default company if none exists
            $newCompany = Company::create([
                'name' => 'Default Company',
                'domain' => 'default.local'
            ]);
            $user->company_id = $newCompany->id;
            $user->save();
            return $newCompany;
        });
    }

    public function tasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class);
    }

    // ==================== ROLE METHODS ====================
    
    /**
     * Check if user is an owner
     */
    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    /**
     * Check if user is a manager (or owner)
     */
    public function isManager(): bool
    {
        return $this->role === 'manager' || $this->isOwner();
    }

    /**
     * Check if user is an employee (all roles can view basic data)
     */
    public function isEmployee(): bool
    {
        return in_array($this->role, ['employee', 'manager', 'owner']);
    }

    /**
     * Check if user can manage projects
     */
    public function canManageProjects(): bool
    {
        return $this->isManager();
    }

    /**
     * Check if user can manage tasks
     */
    public function canManageTasks(): bool
    {
        return $this->isManager();
    }

    /**
     * Check if user can delete projects
     */
    public function canDeleteProjects(): bool
    {
        return $this->isOwner();
    }

    /**
     * Check if user can assign users to tasks
     */
    public function canAssignUsers(): bool
    {
        return $this->isManager();
    }

    /**
     * Check if user can view reports
     */
    public function canViewReports(): bool
    {
        return $this->isManager();
    }

    /**
     * Check if user can manage company settings
     */
    public function canManageCompany(): bool
    {
        return $this->isOwner();
    }

    /**
     * Get the role display name
     */
    public function getRoleName(): string
    {
        $roles = [
            'owner' => 'Owner',
            'manager' => 'Manager',
            'employee' => 'Employee',
        ];
        
        return $roles[$this->role] ?? 'Unknown';
    }

    /**
     * Check if user has a specific role
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Check if user has any of the given roles
     */
    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->role, $roles);
    }
}