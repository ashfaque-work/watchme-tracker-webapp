<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use App\Models\TrackerLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminController extends Controller
{
    //Internal function for adding time
    private function addTimes($time1, $time2)
    {
        $time1 = Carbon::createFromTimeString($time1);
        $time2 = Carbon::createFromTimeString($time2);
        $totalTime = $time1->copy()->addHours($time2->hour)->addMinutes($time2->minute)->addSeconds($time2->second);

        return $totalTime->format('H:i:s');
    }

    //Internal function for converting duration to sec
    private function convertDurationToSeconds($duration)
    {
        list($hours, $minutes, $seconds) = array_pad(explode(':', $duration), 3, 0);
        return ($hours * 3600) + ($minutes * 60) + $seconds;
    }

    //Internal function for converting sec to hrs and min
    private function convertSecondsToHoursAndMinutes($seconds)
    {
        if ($seconds == 0) {
            return '00:00';
        }

        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds / 60) % 60);

        return sprintf('%02d:%02d', $hours, $minutes);
    }

    //Get user logs for admin and managers
    public function getUserLog(Request $request)
    {
        $currUser = $request->user();
        $isAuthorizedRole = $currUser->hasRole(['admin', 'super-admin', 'manager', 'hr']);
        if (!$isAuthorizedRole) {
            abort(403, 'Unauthorized');
        }

        if ($currUser->hasRole('manager')) {
            $allUser = User::where('manager_id', $currUser->id)->where('status', 'regular')->get();
        } else {
            $allUser = User::where('status', 'regular')
                ->whereDoesntHave('roles', function ($query) {
                    $query->where('name', 'admin');
                })->get();
        }

        $selectedUserId = $request->userId ?? ($allUser->isNotEmpty() ? $allUser->first()->id : null);

        $from = Carbon::now()->startOfDay();
        $to = Carbon::now()->endOfDay();

        if ($request->start_date && $request->end_date) {
            $from = Carbon::parse($request->start_date)->startOfDay();
            $to = Carbon::parse($request->end_date)->endOfDay();
        }

        $logs = TrackerLog::where('user_id', $selectedUserId)
            ->whereBetween('start_time', [$from, $to])
            ->orderBy('start_time', 'desc')
            ->get();

        $logsArray = [];
        $totalTime = '00:00:00';
        foreach ($logs as $log) {
            $dateWiseLog = [];
            $timeLogs = $log?->time_logs;
            if ($timeLogs) {
                $timeLogs = json_decode($timeLogs, true);
            }
            $logDate = Carbon::parse($log['start_time'])->format('d F');

            $id = "a0";
            while (true) {
                if (array_key_exists($id, $timeLogs)) {
                    $screenshots = $log->screenshot?->screenshots;
                    if ($screenshots) {
                        $screenshots = json_decode($screenshots, true);

                        $startTime = Carbon::parse($timeLogs[$id]['start_time']);
                        $endTime = Carbon::parse($timeLogs[$id]['end_time']);
                        $images = [];

                        foreach ($screenshots as $screenshot) {
                            $captureTime = Carbon::parse($screenshot['capture_time']);
                            if ($captureTime->between($startTime, $endTime)) {
                                $images[] = ['image' => $screenshot['image'], 'capture_time' => $screenshot['capture_time']];
                            }
                        }
                        $dateWiseLog[] = [
                            'timeLogId' => $id,
                            'startTime' => $startTime->format('Y-m-d H:i:s'),
                            'endTime' => $endTime->format('Y-m-d H:i:s'),
                            'fstartTime' => $startTime->format('h:i:s A'),
                            'fendTime' => $endTime->format('h:i:s A'),
                            'duration' => $timeLogs[$id]['duration'],
                            'type' => $timeLogs[$id]['type'],
                            'description' => $timeLogs[$id]['description'] ?? '',
                            'screenshot' => $images,
                        ];
                    } else {
                        $startTime = Carbon::parse($timeLogs[$id]['start_time']);
                        $endTime = Carbon::parse($timeLogs[$id]['end_time']);
                        $dateWiseLog[] = [
                            'timeLogId' => $id,
                            'startTime' => $startTime->format('Y-m-d H:i:s'),
                            'endTime' => $endTime->format('Y-m-d H:i:s'),
                            'fstartTime' => $startTime->format('h:i:s A'),
                            'fendTime' => $endTime->format('h:i:s A'),
                            'duration' => $timeLogs[$id]['duration'],
                            'type' => $timeLogs[$id]['type'],
                            'description' => $timeLogs[$id]['description'] ?? '',
                            'screenshot' => [],
                        ];
                    }
                    $subId = substr($id, 1);
                    $id = "a" . (string) ((int) $subId + 1);
                } else {
                    break;
                }
            }

            $manualId = "m0";
            while (true) {
                if (array_key_exists($manualId, $timeLogs)) {
                    $startTime = Carbon::parse($timeLogs[$manualId]['start_time']);
                    $endTime = Carbon::parse($timeLogs[$manualId]['end_time']);
                    $dateWiseLog[] = [
                        'timeLogId' => $manualId,
                        'startTime' => $startTime->format('Y-m-d H:i:s'),
                        'endTime' => $endTime->format('Y-m-d H:i:s'),
                        'fstartTime' => $startTime->format('h:i:s A'),
                        'fendTime' => $endTime->format('h:i:s A'),
                        'duration' => $timeLogs[$manualId]['duration'],
                        'type' => $timeLogs[$manualId]['type'],
                        'description' => $timeLogs[$manualId]['description'] ?? '',
                        'screenshot' => []
                    ];

                    $subId = substr($manualId, 1);
                    $manualId = "m" . (string) ((int) $subId + 1);
                } else {
                    break;
                }
            }

            $logDateTime = Carbon::parse($log['start_time'])->format('Y-m-d H:i');
            [$hours, $minutes, $seconds] = explode(':', $log->elapsed_time);
            $logsArray[$logDate]["log"] = $dateWiseLog;
            $logsArray[$logDate]["dateTime"] = $logDateTime;
            $logsArray[$logDate]["elapsedTime"] = "{$hours} hr {$minutes} min {$seconds} sec";
            $logsArray[$logDate]["logId"] = $log->id;
        }

        if ($request->ajax()) {
            return response()->json([
                "selectedUserId" => $selectedUserId,
                "allUser" => $allUser,
                "logsArray" => $logsArray,
            ]);
        } else {
            if ($currUser->hasRole('manager')) {
                return view('manager.userLogs', compact('selectedUserId', 'allUser', 'logsArray'));
            }
            return view('admin.userLogs', compact('selectedUserId', 'allUser', 'logsArray'));
        }
    }

    //Get users list in admin page
    public function userList()
    {
        // Get all users who do not have the 'admin' role
        $users = User::whereDoesntHave('roles', function ($query) {
            $query->where('name', 'admin');
        })->with(['shift', 'manager'])->latest()->get();
        // $users = User::with(['shift', 'manager'])->latest()->get();

        $managers = User::role('manager')->get();
        $shiftNames = Shift::pluck('name');
        // $roles = Role::all();
        $roles = Role::whereNotIn('name', ['admin', 'super-admin'])->get();

        return view('admin.users', compact('users', 'managers', 'shiftNames', 'roles'));
    }

    //Assign manager to user by admin
    public function assignManagerToUser(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'manager_id' => 'nullable|exists:users,id',
        ]);

        $user = User::findOrFail($request->user_id);
        $user->manager_id = $request->manager_id;
        $user->save();

        return redirect()->route('admin.user-list')->with('success', 'Manager assigned successfully.');
    }

    //Get associated users for manager
    public function userListForManager(Request $request)
    {
        $users = User::where('manager_id', $request->user()->id)->latest()->paginate(10);
        return view('manager.user-list', compact('users'));
    }

    //Assign role to a user
    public function assignRole(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role_id' => 'nullable|exists:roles,id',
        ]);

        $user = User::findOrFail($request->user_id);

        if ($request->role_id) {
            $role = Role::findOrFail($request->role_id);

            // Prevent assigning admin or super-admin roles
            if (in_array($role->name, ['admin', 'super-admin'])) {
                return redirect()->route('admin.user-list')->withErrors(['role' => 'This role cannot be assigned.']);
            }

            // Detach all roles and assign the selected role
            $user->roles()->sync([$role->id]);
        } else {
            // Detach all roles to remove any assigned role
            $user->roles()->detach();
        }

        return redirect()->route('admin.user-list')->with('success', 'Role updated successfully.');
    }

    //Create a new user
    public function createUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role_id' => 'nullable|exists:roles,id', // Role is optional
            'shift_id' => 'required|string|exists:shifts,name', // Validate the shift name
            'manager_id' => 'nullable|exists:users,id',
        ]);

        $shift = Shift::where('name', $request->shift_id)->firstOrFail();

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->shift_id = $shift->id;
        $user->manager_id = $request->manager_id;
        $user->save();

        // Assign role to the user if selected
        if ($request->role_id) {
            $role = Role::findOrFail($request->role_id);
            $user->roles()->attach($role);
        }

        // Send email to user if checked
        if ($request->send_email) {
            // Mail::to($user->email)->send(new UserCreated($user));
        }

        return redirect()->route('admin.user-list')->with('success', 'User created successfully.');
    }

    //Update User status
    public function updateUserStatus(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'status' => 'required|in:regular,inactive',
        ]);

        $user = User::findOrFail($request->input('user_id'));
        $user->status = $request->input('status');
        $user->save();

        return redirect()->back()->with('success', 'User status updated successfully.');
    }

    //Update user's password
    public function updateUserPassword(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::find($request->user_id);
        $user->password = Hash::make($request->new_password);
        $user->save();

        return redirect()->back()->with('success', 'Password updated successfully.');
    }

    //Show manual entries of a user
    public function manualEntries(Request $request)
    {
        $selectedMonth = $request->input('month', Carbon::now()->format('Y-m'));
        $search = $request->input('search');
        $sortBy = $request->input('sort_by', 'name'); // Default sort by 'name'
        $sortOrder = $request->input('sort_order', 'asc'); // Default sort order 'asc'

        $startDate = Carbon::parse($selectedMonth)->startOfMonth();
        $endDate = Carbon::parse($selectedMonth)->endOfMonth();

        // Add search functionality
        $usersQuery = User::whereDoesntHave('roles', function ($query) {
            $query->where('name', 'admin');
        });

        if ($search) {
            $usersQuery->where(function ($query) use ($search) {
                $query->where('name', 'LIKE', '%' . $search . '%')
                    ->orWhere('email', 'LIKE', '%' . $search . '%');
            });
        }

        // Fetch and calculate total and manual hours for each user
        $users = $usersQuery->with(['shift'])
            ->get() // Retrieve all users first
            ->map(callback: function ($user) use ($startDate, $endDate) {
                $trackerLogs = TrackerLog::where('user_id', $user->id)
                    ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                    ->get();

                $totalSeconds = 0;
                $manualSeconds = 0;

                foreach ($trackerLogs as $log) {
                    $totalSeconds += $this->convertDurationToSeconds($log->elapsed_time);

                    $timeLogs = json_decode($log->time_logs, true);

                    if (is_array($timeLogs)) {
                        foreach ($timeLogs as $entry) {
                            if ($entry['type'] === 'entry') {
                                $manualSeconds += $this->convertDurationToSeconds($entry['duration']);
                            }
                        }
                    }
                }

                return [
                    'user' => $user,
                    'total_hours' => $this->convertSecondsToHoursAndMinutes($totalSeconds),
                    'total_manual_hours' => $this->convertSecondsToHoursAndMinutes($manualSeconds),
                    'manual_seconds' => $manualSeconds,
                    'total_seconds' => $totalSeconds
                ];
            });

        // Apply sorting to the entire collection
        $users = $users->sortBy($sortBy === 'total_manual_hours' ? 'manual_seconds' : 'total_seconds', SORT_REGULAR, $sortOrder === 'desc');

        // Paginate after sorting
        $paginatedUsers = $this->paginateCollection($users, 8);

        return view('admin.manualEntries', [
            'userData' => $paginatedUsers,
            'selectedMonth' => $selectedMonth,
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder,
        ]);
    }

    // Helper function to paginate a collection
    private function paginateCollection($items, $perPage)
    {
        $page = request()->get('page', 1);
        $offset = ($page - 1) * $perPage;

        return new \Illuminate\Pagination\LengthAwarePaginator(
            $items->slice($offset, $perPage),
            $items->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }

    //Update Total working hours from 01 to 08 Aug,2024
    public function updateTotalTime(Request $request)
    {
        // Define the date range
        $startDate = Carbon::create(2024, 8, 1)->startOfDay();
        $endDate = Carbon::create(2024, 8, 8)->endOfDay();

        // Define the new elapsed time
        $newElapsedTime = '08:30:00'; // 8.5 hours

        // Update all records within the date range
        TrackerLog::whereBetween('date', [$startDate, $endDate])
            ->update(['elapsed_time' => $newElapsedTime]);

        return redirect()->back()->with('success', 'Elapsed time updated successfully for logs between August 1, 2024, and August 8, 2024.');
    }

}
