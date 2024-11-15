<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, SoftDeletes, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'shift_id',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'email_verified_at',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function trackerLogs()
    {
        return $this->hasMany(TrackerLog::class);
    }

    public function shift() {
        return $this->belongsTo(Shift::class);
    }

    public function userLog()
    {
        return $this->hasOne(UserLog::class);
    }

    public function todayLog()
    {
        return $this->hasOneThrough(
            TrackerLog::class, // The final model we want to access
            UserLog::class,    // The intermediate model
            'user_id',         // Foreign key on the UserLog model
            'id',              // Foreign key on the TrackerLog model
            'id',              // Local key on the User model
            'today_log_id'     // Local key on the UserLog model
        );
    }

    // public function teamMembers()
    // {
    //     return $this->hasMany(TeamMember::class, 'user_id');
    // }

    // public function teamLeadOf()
    // {
    //     return $this->hasMany(TeamMember::class, 'team_lead_id');
    // }

    // public function timeUpdates()
    // {
    //     return $this->hasMany(ManualMinutesEntry::class);
    // }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function managedUsers()
    {
        return $this->hasMany(User::class, 'manager_id');
    }
}
