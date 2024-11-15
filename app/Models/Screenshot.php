<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Screenshot extends Model
{
    use HasFactory;

    protected $fillable = [
        "user_id",
        "tracker_log_id",
        "screenshots"
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }
    public function trackerLog() {
        return $this->belongsTo(TrackerLog::class);
    }
}
