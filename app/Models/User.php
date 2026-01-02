<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'organization_id',
        'phone',
        'job_title',
        'department',
        'employee_id',
        'address',
        'timezone',
        'is_active',
        'last_seen_at',
        'availability_status',
        'availability_updated_at',
        'current_latitude',
        'current_longitude',
        'mfa_enabled',
        'mfa_method',
        'mfa_phone',
        'mfa_email',
        'mfa_secret',
        'mfa_confirmed_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'mfa_secret',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_seen_at' => 'datetime',
            'availability_updated_at' => 'datetime',
            'mfa_enabled' => 'boolean',
            'mfa_confirmed_at' => 'datetime',
            'mfa_secret' => 'encrypted',
        ];
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function assignedWorkOrders()
    {
        return $this->hasMany(WorkOrder::class, 'assigned_to_user_id');
    }

    public function requestedWorkOrders()
    {
        return $this->hasMany(WorkOrder::class, 'requested_by_user_id');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'assigned_to_user_id');
    }

    public function passwordHistories()
    {
        return $this->hasMany(PasswordHistory::class);
    }

    public function mfaChallenges()
    {
        return $this->hasMany(MfaChallenge::class);
    }
}
