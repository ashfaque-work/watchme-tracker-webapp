<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TrackerLog;
use App\Models\User;
use Carbon\Carbon;

class InitialLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:initial-logs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = User::where('status', 'regular')->get();
        $currentDate = Carbon::now()->format('Y-m-d');
        $currentDateTime = Carbon::now()->format('Y-m-d H:i:s');
        
        if (count($users) > 0) {
            foreach ($users as $user) {
                $logExists = TrackerLog::where('user_id', $user->id)
                    ->whereDate('date', $currentDate)
                    ->exists();

                if (!$logExists) {
                    TrackerLog::updateOrCreate(
                        [
                            'user_id' => $user->id,
                            'date' => $currentDate,
                        ],
                        [
                            'status' => 'initial',
                            'start_time'=> $currentDateTime,
                            'elapsed_time' => "00:00:00",
                            'time_logs' => json_encode([]),
                        ]
                    );
                }
            }
        }
    }
}
