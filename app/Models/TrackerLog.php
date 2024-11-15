<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class TrackerLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'project_name',
        'description',
        'tracked_times',
        'date',
        'status',
        'start_time',
        'end_time',
        'elapsed_time',
        'time_logs',
        'screenshots',
        'current_log_id',

        'last_active',
        'last_pause'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function screenshot() {
        return $this->hasOne(Screenshot::class);
    }
    public function getScreenshotsAttribute($value)
    {
        return json_decode($value, true);
    }
    public function getTimeLogAttribute($value)
    {
        return json_decode($value, true);
    }

    public static function getUserScreenshots($userId, $date)
    {
        return self::where('user_id', $userId)
            ->whereDate('created_at', $date)
            ->pluck('screenshots');
    }

    public static function getUserActivities($selectedUserId, $startDate, $endDate)
    {
        return self::where('user_id', $selectedUserId)
            ->whereBetween('start_time', [$startDate, $endDate])
            ->orderByDesc('start_time')
            ->get();
    }
}
