<?php

namespace App\Exports;

use App\Models\TrackerLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromView;

class TimesheetExport implements FromView
{
    protected $userId;
    protected $shiftId;
    protected $month;
    protected $year;

    public function __construct($userId, $shiftId, $month, $year)
    {
        $this->userId = $userId;
        $this->shiftId = $shiftId;
        $this->month = $month;
        $this->year = $year;
    }

    public function view(): View
    {
        $query = User::where('status', 'regular');

        if ($this->userId) {
            $query->where('id', $this->userId);
        }

        if ($this->shiftId) {
            $query->where('shift_id', $this->shiftId);
        }

        $filteredUsers = $query->get();
        $daysInMonth = Carbon::createFromDate($this->year, $this->month, 1)->daysInMonth;
        $timesheet = [];

        foreach ($filteredUsers as $user) {
            $userTimesheet = [
                'name' => $user->name,
                'total' => 0,
                'days' => [],
            ];

            for ($day = 1; $day <= $daysInMonth; $day++) {
                $date = Carbon::createFromDate($this->year, $this->month, $day);
                $totalSeconds = TrackerLog::where('user_id', $user->id)
                    ->whereDate('date', $date)
                    ->sum(DB::raw('TIME_TO_SEC(elapsed_time)'));

                $formattedTime = $this->convertSecondsToHoursAndMinutes($totalSeconds);
                $userTimesheet['days'][$date->format('d M Y')] = $totalSeconds == 0 ? '-' : $formattedTime;
                $userTimesheet['total'] += $totalSeconds;
            }

            $userTimesheet['total'] = $this->convertSecondsToHoursAndMinutes($userTimesheet['total']);
            $timesheet[] = $userTimesheet;
        }

        return view('exports.timesheet', [
            'timesheet' => $timesheet,
            'daysInMonth' => $daysInMonth,
            'year' => $this->year,
            'month' => $this->month,
        ]);
    }

    private function convertSecondsToHoursAndMinutes($seconds)
    {
        if ($seconds == 0) {
            return '-';
        }

        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds / 60) % 60);

        return sprintf('%02d:%02d', $hours, $minutes);
    }
}
