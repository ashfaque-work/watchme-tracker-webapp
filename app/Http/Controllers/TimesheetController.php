<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use App\Models\TrackerLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TimesheetExport;

class TimesheetController extends Controller
{
    public function index(Request $request)
    {
        $allUsers = User::where('status', 'regular')->get(); // Get all active users for the filter dropdown
        $allShifts = Shift::all();
        $selectedUserId = $request->input('user_id', null);
        $selectedShiftId = $request->input('shift_id', null);
        $selectedMonth = $request->input('month', Carbon::now()->month);
        $selectedYear = $request->input('year', Carbon::now()->year);
        $daysInMonth = Carbon::createFromDate($selectedYear, $selectedMonth, 1)->daysInMonth;

        $query = User::where('status', 'regular'); // Only include active users
        if ($selectedUserId) {
            $query->where('id', $selectedUserId);
        }

        if ($selectedShiftId) {
            $query->where('shift_id', $selectedShiftId);
        }

        $filteredUsers = $query->paginate(20);
        $timesheet = [];

        foreach ($filteredUsers as $user) {
            $userTimesheet = [
                'name' => $user->name,
                'total' => 0,
                'days' => [],
            ];

            for ($day = 1; $day <= $daysInMonth; $day++) {
                $date = Carbon::createFromDate($selectedYear, $selectedMonth, $day);
                $totalSeconds = TrackerLog::where('user_id', $user->id)
                    ->whereDate('date', $date)
                    ->sum(DB::raw('TIME_TO_SEC(elapsed_time)'));

                $formattedTime = $this->convertSecondsToHoursAndMinutes($totalSeconds);
                $userTimesheet['days'][$day] = $totalSeconds == 0 ? '-' : $formattedTime;
                $userTimesheet['total'] += $totalSeconds;
            }

            $userTimesheet['total'] = $this->convertSecondsToHoursAndMinutes($userTimesheet['total']);
            $timesheet[] = $userTimesheet;
        }

        $daysHeader = $this->generateDaysHeader($selectedYear, $selectedMonth, $daysInMonth);

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.partials.timesheet_body', compact('timesheet', 'daysInMonth', 'selectedYear', 'selectedMonth'))->render(),
                'header' => $daysHeader,
                'pagination' => (string) $filteredUsers->appends($request->all())->links(),
            ]);
        }

        return view('admin.timesheet', compact('timesheet', 'daysInMonth', 'filteredUsers', 'selectedUserId', 'selectedShiftId', 'selectedMonth', 'selectedYear', 'allUsers', 'allShifts'));
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

    private function generateDaysHeader($year, $month, $daysInMonth)
    {
        $header = '<th>Employee</th>';
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $dayName = Carbon::createFromDate($year, $month, $day)->format('D');
            $header .= "<th>{$day}<br>{$dayName}</th>";
        }
        $header .= '<th>Total</th>';

        return $header;
    }

    public function export(Request $request)
    {
        $selectedUserId = $request->input('user_id', null);
        $selectedShiftId = $request->input('shift_id', null);
        $selectedMonth = $request->input('month', Carbon::now()->month);
        $selectedYear = $request->input('year', Carbon::now()->year);

        return Excel::download(new TimesheetExport($selectedUserId, $selectedShiftId, $selectedMonth, $selectedYear), 'timesheet.xlsx');
    }
    
}
