<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\TrackerLog;
use App\Models\Screenshot;
use App\Models\User;
use App\Services\GoogleDriveService;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class TrackerLogController extends Controller
{
    protected $googleDriveService;

    public function __construct(GoogleDriveService $googleDriveService)
    {
        $this->googleDriveService = $googleDriveService;
    }

    private function addTimes($time1, $time2)
    {
        $time1 = Carbon::createFromTimeString($time1);
        $time2 = Carbon::createFromTimeString($time2);
        $totalTime = $time1->copy()->addHours($time2->hour)->addMinutes($time2->minute)->addSeconds($time2->second);

        return $totalTime->format('H:i:s');
    }

    private function getTimeStampDifference($startTime, $endTime)
    {
        $start = Carbon::parse($startTime);
        $end = Carbon::parse($endTime);

        $differenceInSeconds = $start->diffInSeconds($end);

        return gmdate('H:i:s', $differenceInSeconds);
    }

    public function checkTimeConflict($from, $to, $logs)
    {
        foreach ($logs as $log) {
            $startTime = Carbon::parse($log['start_time']);
            $endTime = Carbon::parse($log['end_time']);

            if ($from->greaterThan($startTime) && $from->lessThan($endTime)) {
                return true;
            }

            if ($to->greaterThan($startTime) && $to->lessThan($endTime)) {
                return true;
            }

            if ($from->lessThan($startTime) && $to->greaterThan($endTime)) {
                return true;
            }
        }
        return false;
    }


    public function updateLog(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer',
                'tracked_time' => ['required', 'regex:/^(?:[01]\d|2[0-3]):[0-5]\d:[0-5]\d$/'],
                'date' => ['required', 'date_format:Y-m-d'],
                'start_time' => 'required|date',
                'end_time' => 'required|date',
                'type' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

            $user = auth()->user();
            $reqStartTime = Carbon::parse($request->start_time);
            $reqEndTime = Carbon::parse($request->end_time);

            // where start time if after end time
            if ($reqStartTime->greaterThan($reqEndTime)) {
                Log::debug('start_time and end_time are wrong =>', $request->all());
                return response()->json([
                    'status' => 400,
                    'message' => "start_time and end_time are wrong"
                ], 400);
            }

            $duration = $reqStartTime->diff($reqEndTime)->format('%H:%I:%S');

            // for edge case where tracker faced any issue while ShutDown
            if ($duration > '01:00:00') {
                Log::debug('duration greater than 1 hours=>', $request->all());
                return response()->json([
                    'status' => 400,
                    'message' => "duration greater than 1 hours"
                ], 400);
            }

            $currentDate = Carbon::now()->format('Y-m-d');
            $previousDate = Carbon::now()->subDay()->format('Y-m-d');

            $latestLog = TrackerLog::where('user_id', $user->id)
                ->whereDate('created_at', $currentDate)
                ->orderBy('created_at', 'desc')
                ->first();


            $currentTime = Carbon::now()->format('H:i:s');
            $start = '00:00:00';
            $end = '08:00:00';

            $shift = $user->shift?->name;
            if ($shift && ($shift === 'afternoon' || $shift === 'evening' || $shift === 'night') && ($currentTime >= $start && $currentTime <= $end)) {
                $latestLog = TrackerLog::where('user_id', $user->id)
                    ->whereDate('created_at', $previousDate)
                    ->orderBy('created_at', 'desc')
                    ->first();

            } elseif ($shift && ($shift === 'morning') && ($currentTime >= $start && $currentTime <= $end)) {
                return response()->json([
                    'status' => 400,
                    'message' => 'Morning Shift cannot exceed to next date or start early.'
                ], 400);
            }

            if (!$latestLog) {
                if ($shift && ($shift === 'afternoon' || $shift === 'evening' || $shift === 'night') && ($request->start_time >= $start && $request->start_time <= $end)) {
                    Log::debug('For the user who forget to start tracker till 00:00:00 =>', $request->all());
                }

                $currentLogId = "a0";
                $newTimeLog = [
                    $currentLogId => [
                        'start_time' => $request->start_time,
                        'end_time' => $request->end_time,
                        'duration' => $duration,
                        'type' => $request->type
                    ]
                ];
                $trackerLog = TrackerLog::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'date' => $request->date,
                    ],
                    [
                        'status' => 'started',
                        'start_time' => $request->start_time,
                        'end_time' => $request->end_time,
                        'elapsed_time' => $duration,
                        'current_log_id' => $currentLogId,
                        'time_logs' => json_encode($newTimeLog),
                        'last_active' => $request->start_time,
                    ]
                );

                return response()->json([
                    'status' => 'success',
                    'message' => 'Log Created',
                    'elapsed_time' => $duration,
                    'log_id' => $trackerLog->id
                ], 201);

            } elseif ($latestLog) {

                $totalElapsedTime = $this->addTimes($latestLog->elapsed_time, $duration);

                $currentLogId = $latestLog->current_log_id;

                if ($currentLogId == null) {
                    $currentLogId = "a0";

                    $newTimeLog = [
                        $currentLogId => [
                            'start_time' => $request->start_time,
                            'end_time' => $request->end_time,
                            'duration' => $duration,
                            'type' => $request->type,
                            'status' => 'first',
                        ]
                    ];

                    $latestLog->update([
                        'status' => 'started',
                        'end_time' => $request->end_time,
                        'elapsed_time' => $totalElapsedTime,
                        'current_log_id' => $currentLogId,
                        'time_logs' => json_encode($newTimeLog),
                        'last_active' => $request->start_time,
                    ]);
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Log Entry Updated',
                        'elapsed_time' => $totalElapsedTime,
                        'log_id' => $latestLog->id
                    ]);
                }

                $currentTimeLog = json_decode($latestLog->time_logs, true);

                $lastEndTime = Carbon::parse($currentTimeLog[$currentLogId]['end_time']);

                $conflict = $this->checkTimeConflict($reqStartTime, $reqEndTime, $currentTimeLog);
                if ($conflict) {
                    Log::debug('Time Conflict =>', $request->all());
                    Log::debug('Time Log =>', $currentTimeLog);
                    return response()->json([
                        'status' => 400,
                        'message' => 'Conflict in Time!'
                    ], 400);
                }

                if ($reqStartTime->eq($lastEndTime)) {
                    //dont create new time log, update the same time log
                    if ($currentTimeLog[$currentLogId]) {
                        $totalDuration = $this->addTimes($currentTimeLog[$currentLogId]["duration"], $duration);

                        $currentTimeLog[$currentLogId]["end_time"] = $request->end_time;
                        $currentTimeLog[$currentLogId]["duration"] = $totalDuration;
                        $currentTimeLog[$currentLogId]["type"] = $request->type;
                    }
                    $status = ($request->type !== 'partial') ? 'stopped' : 'running';
                    $latestLog->update([
                        'status' => $status,
                        'end_time' => $request->end_time,
                        'elapsed_time' => $totalElapsedTime,
                        'current_log_id' => $currentLogId,
                        'time_logs' => json_encode($currentTimeLog),
                    ]);
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Log Updated',
                        'elapsed_time' => $totalElapsedTime,
                        'log_id' => $latestLog->id
                    ]);
                } elseif ($reqStartTime->gt($lastEndTime)) {
                    // after time break
                    $subId = substr($currentLogId, 1);
                    $currentLogId = "a" . (string) ((int) $subId + 1);

                    $untrackedDuration = $reqStartTime->diff($lastEndTime)->format('%H:%I:%S');
                    $currentTimeLog[$currentLogId] = [
                        'start_time' => $lastEndTime->format('Y-m-d H:i:s'),
                        'end_time' => $reqStartTime->format('Y-m-d H:i:s'),
                        'duration' => $untrackedDuration,
                        'type' => 'untracked',
                    ];

                    $currentLogId = "a" . (string) ((int) $subId + 2);
                    $currentTimeLog[$currentLogId] = [
                        'start_time' => $request->start_time,
                        'end_time' => $request->end_time,
                        'duration' => $duration,
                        'type' => $request->type,
                    ];

                    $status = ($request->type !== 'partial') ? 'stopped' : 'running';
                    $latestLog->update([
                        'status' => $status,
                        'end_time' => $request->end_time,
                        'elapsed_time' => $totalElapsedTime,
                        'current_log_id' => $currentLogId,
                        'time_logs' => json_encode($currentTimeLog),
                        'last_active' => $request->start_time,
                    ]);
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Log Entries Updated',
                        'elapsed_time' => $totalElapsedTime,
                        'log_id' => $latestLog->id
                    ]);
                } else {
                    Log::debug("request start time and last endtime conflict: ", $request->all());
                    return response()->json([
                        "status" => "error",
                        "message" => "request start time and last endtime conflict"
                    ], 400);
                }
            }
        } catch (Exception $e) {
            Log::error('Error with update log ' . $e);
            return response()->json(['status' => false, 'error' => $e->getMessage()], 500);
        }
    }

    private function googleUpload($currentScreenshots, $base64, $reqCaptureAt, $user)
    {
        $imageBinary = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64));
        $words = explode(' ', $user->name);
        $formattedName = implode('_', $words) . '_' . $user->id;
        $filename = 'img_' . now()->format('Ymd_His') . '.jpg';
        $fileId = $this->googleDriveService->uploadToDrive($formattedName, $filename, $imageBinary);

        if (!$fileId) {
            Log::error('message => Failed to upload image');
            return [];
        } else {
            $processedScreenshot = [
                'image' => $fileId,
                'capture_time' => $reqCaptureAt,
                'file_name' => $filename,
                'folder_name' => $formattedName,
            ];
        }
        $currentScreenshots[] = $processedScreenshot;
        return $currentScreenshots;
    }

    public function updateScreenshot(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|integer',
                'user_id' => 'required|integer',
                'screenshot' => 'required|string',
                'captured_at' => ['required', 'date_format:Y-m-d H:i:s'],
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

            $user = auth()->user();
            $currentDate = Carbon::now()->format('Y-m-d');
            $previousDate = Carbon::now()->subDay()->format('Y-m-d');

            // $latestLog = $user->trackerLogs()->whereDate('start_time', $currentDate)->first();
            $latestLog = TrackerLog::where('user_id', $user->id)
                ->whereDate('created_at', $currentDate)
                ->orderBy('created_at', 'desc')
                ->first();

            $currentTime = Carbon::now()->format('H:i:s');
            $start = '00:00:00';
            $end = '08:00:00';

            $shift = $user->shift?->name;
            if ($shift && ($shift === 'afternoon' || $shift === 'evening' || $shift === 'night') && ($currentTime >= $start && $currentTime <= $end)) {
                // $latestLog = $user->trackerLogs()->whereDate('start_time', $previousDate)->first();
                $latestLog = TrackerLog::where('user_id', $user->id)
                    ->whereDate('created_at', $previousDate)
                    ->orderBy('created_at', 'desc')
                    ->first();
            } elseif ($shift && ($shift === 'morning') && ($currentTime >= $start && $currentTime <= $end)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Morning Shift cannot exceed to next date or start early.'
                ]);
            }

            if (!$latestLog) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No log related to this user',
                ], 404);
            }

            $reqScreenshot = $request->screenshot;
            $reqCaptureAt = $request->captured_at;

            $screenshot = Screenshot::where('tracker_log_id', $latestLog->id)->first();

            if ($screenshot) {
                $currentScreenshots = json_decode($screenshot->screenshots, true);
                $currentScreenshots = $this->googleUpload($currentScreenshots, $reqScreenshot, $reqCaptureAt, $user);
                if (count($currentScreenshots) > 0) {
                    $screenshot->screenshots = json_encode($currentScreenshots);
                    $screenshot->save();
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Screenshot Updated'
                    ]);
                } else {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Error uploading screenshot'
                    ], 500);
                }
            } else {
                $currentScreenshots = $this->googleUpload([], $reqScreenshot, $reqCaptureAt, $user);
                if (count($currentScreenshots) > 0) {
                    Screenshot::create([
                        "user_id" => $user->id,
                        "tracker_log_id" => $latestLog->id,
                        "screenshots" => json_encode($currentScreenshots),
                    ]);
                } else {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Error uploading screenshot'
                    ], 500);
                }
                return response()->json([
                    'status' => 'success',
                    'message' => 'Screenshot Created'
                ], 201);
            }
        } catch (Exception $e) {
            Log::error('Error with update log ' . $e);
            return response()->json(['status' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function createInitialLog()
    {
        try {
            $currentDate = Carbon::now()->format('Y-m-d');
            $currentDateTime = Carbon::now()->format('Y-m-d H:i:s');
            $user = auth()->user();

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
                        'start_time' => $currentDateTime,
                        'elapsed_time' => "00:00:00",
                        'time_logs' => json_encode([]),
                    ]
                );
                return response()->json([
                    'status' => 'success',
                    'message' => 'Initial Log Created'
                ], 201);
            }
        } catch (Exception $e) {
            Log::error('Error with update log ' . $e);
            return response()->json(['status' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function statusUpdateOnStart(Request $request)
    {
        try {
            $user = auth()->user();
            $shift = $user->shift?->name;
            $currentDate = Carbon::now()->format('Y-m-d');
            $previousDate = Carbon::now()->subDay()->format('Y-m-d');
            $currentTime = Carbon::now()->format('H:i:s');
            $start = '00:00:00';
            $end = '08:00:00';

            $latestLog = TrackerLog::where('user_id', $user->id)
                ->whereDate('created_at', $currentDate)
                ->orderBy('created_at', 'desc')
                ->first();

            if ($shift && ($shift === 'afternoon' || $shift === 'evening' || $shift === 'night') && ($currentTime >= $start && $currentTime <= $end)) {
                $latestLog = TrackerLog::where('user_id', $user->id)
                    ->whereDate('created_at', $previousDate)
                    ->orderBy('created_at', 'desc')
                    ->first();

            } elseif ($shift && ($shift === 'morning') && ($currentTime >= $start && $currentTime <= $end)) {
                return response()->json([
                    'status' => 400,
                    'message' => 'Morning Shift cannot exceed to next date or start early.'
                ], 400);
            }

            if ($latestLog) {
                $latestLog->update([
                    'status' => 'started',
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Status changed to Started'
                ], 200);
            }
        } catch (Exception $e) {
            Log::error('Error with update log ' . $e);
            return response()->json(['status' => false, 'error' => $e->getMessage()], 500);
        }
    }
    public function totalTrackedTime(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'start_date' => 'required|date',
                'end_date' => 'required|date',
                'token' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }
            if ($request->token !== "t3wLGcJ0bJzCVocPMtlY9XdphJ5eLm0z") {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized'
                ], 401);
            }

            $from = Carbon::parse($request->start_date)->startOfDay();
            $to = Carbon::parse($request->end_date)->endOfDay();
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not found!'
                ], 404);
            }

            $userId = $user->id;
            $logs = TrackerLog::where('user_id', $userId)
                ->whereBetween('start_time', [$from, $to])
                ->orderBy('start_time', 'desc')
                ->get();

            if (count($logs) == 0) {
                return response()->json([
                    'status' => 'success',
                    'totalTrackedTime' => 0,
                    'message' => 'No log found!'
                ], 200);
            }

            $totalSeconds = 0;

            foreach ($logs as $log) {
                list($hours, $minutes, $seconds) = explode(':', $log->elapsed_time);
                $totalSeconds += $hours * 3600 + $minutes * 60 + $seconds;
            }

            return response()->json([
                'status' => 'success',
                'totalTrackedTime' => $totalSeconds
            ], 200);
        } catch (Exception $e) {
            Log::error('Error with update log ' . $e);
            return response()->json(['status' => 'error', 'error' => $e->getMessage()], 500);
        }
    }

    public function trackerState(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'date' => 'required|date',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not found!'
                ], 404);
            }

            $userId = $user->id;
            $startOfDay = Carbon::parse($request->date)->startOfDay();
            $endOfDay = Carbon::parse($request->date)->endOfDay();

            $log = TrackerLog::where('user_id', $userId)
                ->whereBetween('start_time', [$startOfDay, $endOfDay])
                ->orderBy('start_time', 'desc')
                ->first();

            if (!$log) {
                return response()->json([
                    'status' => 'error',
                    'massage' => 'No logs on this date.'
                ], 404);
            }
            $statusMap = [
                'started' => 'started',
                'running' => 'running',
                'stopped' => 'stopped',
            ];

            $trackerState = $statusMap[$log->status] ?? null;

            if (!$trackerState) {
                return response()->json([
                    'status' => 'success',
                    'tracker_state' => 'not_started',
                    'massage' => ''
                ], 200);
            }

            return response()->json([
                'status' => 'success',
                'tracker_state' => $trackerState,
                'massage' => ''
            ], 200);
        } catch (Exception $e) {
            Log::error('Error with update log ' . $e);
            return response()->json(['status' => 'error', 'error' => $e->getMessage()], 500);
        }
    }
}
