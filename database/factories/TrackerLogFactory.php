<?php

namespace Database\Factories;

use App\Models\TrackerLog;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class TrackerLogFactory extends Factory
{
    protected $model = TrackerLog::class;

    public function definition()
    {
        $currentDate = Carbon::now()->format('Y-m-d');
        $currentDateTime = Carbon::now();

        return [
            'user_id' => 2,
            'status' => 'first',
            'date' => $currentDate,
            'start_time' => $currentDateTime->format('Y-m-d H:i:s'),
            'end_time' => $currentDateTime->format('Y-m-d H:i:s'), 
        
            'elapsed_time' => "00:00:00",
            'time_logs' => json_encode([
                'a0' => [
                    'start_time' => $currentDateTime->format('Y-m-d H:i:s'),
                    'end_time' => $currentDateTime->format('Y-m-d H:i:s'),
                    'duration' => "00:00:00",
                    'type' => "first",
                ]
            ]),
            'current_log_id' => 'a0',
        ];
    }
}
