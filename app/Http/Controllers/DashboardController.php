<?php

namespace App\Http\Controllers;

use App\Models\TrackerLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    //Dashboard according to user roles
    public function index(Request $request)
    {
        $user = Auth::user();
        $roles = $user->roles->pluck('name');

        if ($roles->contains('admin') || $roles->contains('super-admin') || $roles->contains('hr')) {
            // Fetch all users data
            $userIds = User::pluck('id')->toArray();
            $totalUsers = User::count();
            $activeUsers = User::where('status', 'regular')->count();
        } elseif ($roles->contains('manager')) {
            // Fetch only team members data
            $userIds = User::where('manager_id', $user->id)->pluck('id')->toArray();
            $totalUsers = count($userIds);
            $activeUsers = User::whereIn('id', $userIds)->where('status', 'regular')->count();
        } else {
            // Get the user's ID
            $userId = $user->id;

            // Calculate today's total working hours
            $todayStart = Carbon::now()->startOfDay();
            $todayEnd = Carbon::now()->endOfDay();
            $todayTotal = TrackerLog::where('user_id', $userId)
                ->whereBetween('date', [$todayStart, $todayEnd])
                ->sum(DB::raw('TIME_TO_SEC(elapsed_time)'));

            // Calculate previous week's total hours
            $previousWeekStart = Carbon::now()->subWeek()->startOfWeek();
            $previousWeekEnd = Carbon::now()->subWeek()->endOfWeek();
            $previousWeekTotal = TrackerLog::where('user_id', $userId)
                ->whereBetween('date', [$previousWeekStart, $previousWeekEnd])
                ->sum(DB::raw('TIME_TO_SEC(elapsed_time)'));

            // Calculate current week's total hours
            $currentWeekStart = Carbon::now()->startOfWeek();
            $currentWeekEnd = Carbon::now()->endOfWeek();
            $currentWeekTotal = TrackerLog::where('user_id', $userId)
                ->whereBetween('date', [$currentWeekStart, $currentWeekEnd])
                ->sum(DB::raw('TIME_TO_SEC(elapsed_time)'));

            // Calculate current month's total hours
            $currentMonthStart = Carbon::now()->startOfMonth();
            $currentMonthEnd = Carbon::now()->endOfMonth();
            $currentMonthTotal = TrackerLog::where('user_id', $userId)
                ->whereBetween('date', [$currentMonthStart, $currentMonthEnd])
                ->sum(DB::raw('TIME_TO_SEC(elapsed_time)'));

            // Calculate last month's total hours
            $lastMonthStart = Carbon::now()->subMonth()->startOfMonth();
            $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth();
            $lastMonthTotal = TrackerLog::where('user_id', $userId)
                ->whereBetween('date', [$lastMonthStart, $lastMonthEnd])
                ->sum(DB::raw('TIME_TO_SEC(elapsed_time)'));

            // Convert totals to hours and minutes
            $todayTotalFormatted = $this->convertSecondsToHoursAndMinutes($todayTotal);
            $previousWeekTotalFormatted = $this->convertSecondsToHoursAndMinutes($previousWeekTotal);
            $currentWeekTotalFormatted = $this->convertSecondsToHoursAndMinutes($currentWeekTotal);
            $currentMonthTotalFormatted = $this->convertSecondsToHoursAndMinutes($currentMonthTotal);
            $lastMonthTotalFormatted = $this->convertSecondsToHoursAndMinutes($lastMonthTotal);

            return view('dashboard', compact(
                'todayTotalFormatted',
                'previousWeekTotalFormatted',
                'currentWeekTotalFormatted',
                'currentMonthTotalFormatted',
                'lastMonthTotalFormatted'
            ));
        }

        $inactiveUsers = $totalUsers - $activeUsers;

        // Determine the month for the report
        $reportMonth = $request->get('month', 'this'); // default to 'this' month

        if ($reportMonth === 'prev') {
            $startDate = Carbon::now()->subMonth()->startOfMonth();
            $endDate = Carbon::now()->subMonth()->endOfMonth();
        } else {
            $startDate = Carbon::now()->startOfMonth();
            $endDate = Carbon::now()->endOfMonth();
        }

        // Weekly report
        $oneWeekAgo = Carbon::now()->subWeek();

        $topMembersWeekly = TrackerLog::select('user_id',
            DB::raw("SUM(TIME_TO_SEC(elapsed_time)) as total_time"))
            ->whereIn('user_id', $userIds)
            ->where('date', '>=', $oneWeekAgo)
            ->groupBy('user_id')
            ->orderBy('total_time', 'desc')
            ->limit(5)
            ->get();

        foreach ($topMembersWeekly as $member) {
            $member->total_time = $this->convertSecondsToHoursAndMinutes($member->total_time); // Convert total_time to hours and minutes
        }

        // Monthly report
        $topMembersMonthly = TrackerLog::select('user_id',
            DB::raw("SUM(TIME_TO_SEC(elapsed_time)) as total_time"))
            ->whereIn('user_id', $userIds)
            ->whereBetween('date', [$startDate, $endDate])
            ->groupBy('user_id')
            ->get();

        // Array to store the users with their manual and total times
        $usersMonthly = [];

        foreach ($topMembersMonthly as $member) {
            // Sum up manual time entries
            $manualTimeInSeconds = TrackerLog::where('user_id', $member->user_id)
            ->whereBetween('date', [$startDate, $endDate])
            ->get()
            ->reduce(function ($carry, $item) {
                $timeLogs = json_decode($item->time_logs, true);

                if (is_array($timeLogs)) {
                    $manualEntries = array_filter($timeLogs, function ($log) {
                        return $log['type'] === 'entry';
                    });
                    $manualTime = array_reduce($manualEntries, function ($carry, $entry) {
                        list($hours, $minutes, $seconds) = explode(':', $entry['duration']);
                        return $carry + ($hours * 3600) + ($minutes * 60) + $seconds;
                    }, 0);
                    return $carry + $manualTime;
                }

                return $carry;
            }, 0);

            if ($manualTimeInSeconds > 0) {
                $manualTimeFormatted = $this->convertSecondsToHoursAndMinutes($manualTimeInSeconds); // Convert seconds to hours and minutes
                $totalTimeFormatted = $this->convertSecondsToHoursAndMinutes($member->total_time); // Convert total_time to hours and minutes

                $usersMonthly[] = (object) [
                    'user' => User::find($member->user_id),
                    'total_time' => $totalTimeFormatted,
                    'manual_time' => $manualTimeFormatted,
                    'manual_time_seconds' => $manualTimeInSeconds, // store for sorting
                ];
            }
        }

        // Sort users based on manual time in descending order
        usort($usersMonthly, function ($a, $b) {
            return $b->manual_time_seconds - $a->manual_time_seconds;
        });

        // Limit to top 5 members
        $usersMonthly = array_slice($usersMonthly, 0, 5);
        
        return view('admin.dashboard', compact('totalUsers', 'activeUsers', 'inactiveUsers', 'topMembersWeekly', 'usersMonthly', 'reportMonth'));
    }

    //Internal function for converting seconds to hours and minutes
    private function convertSecondsToHoursAndMinutes($seconds)
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds / 60) % 60);

        return sprintf('%02d:%02d', $hours, $minutes);
    }

}