<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\TrackerLog;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ActivityController extends Controller
{
    //
    public function index(Request $request)
    {
        // dd($request->all());
        try {
            $user = auth()->user();

            $from = Carbon::now()->subDays(7)->startOfDay();
            $to = Carbon::now()->endOfDay();
            if ($request->start_date && $request->end_date) {
                $from = Carbon::parse($request->start_date)->startOfDay();
                $to = Carbon::parse($request->end_date)->endOfDay();
            }

            $logs = $user->trackerLogs()
                ->whereBetween('start_time', [$from, $to])
                ->orderBy('start_time', 'desc')
                ->get();

            $logsArray = [];
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
                        $carbonDuration = Carbon::createFromFormat('H:i:s', $timeLogs[$id]['duration']);
                        $isGreaterThanAnHour = $carbonDuration->greaterThan(Carbon::createFromFormat('H:i:s', '01:00:00'));

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
                                'isGreaterThanAnHour' => $isGreaterThanAnHour,
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
                                'isGreaterThanAnHour' => $isGreaterThanAnHour,
                                'screenshot' => []
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
            // dd($logsArray);
            return view('pages.activity', compact('user', 'logsArray'));
        } catch (Exception $e) {
            Log::error('Error with update log ' . $e);
            return response()->json(['status' => false, 'error' => $e->getMessage()], 500);
        }
    }

    private function addTimes($time1, $time2)
    {
        $time1 = Carbon::createFromTimeString($time1);
        $time2 = Carbon::createFromTimeString($time2);
        $totalTime = $time1->copy()->addHours($time2->hour)->addMinutes($time2->minute)->addSeconds($time2->second);

        return $totalTime->format('H:i:s');
    }

    private function getTimeDifference($startTime, $endTime)
    {
        $start = Carbon::parse($startTime);
        $end = Carbon::parse($endTime);

        $diff = $end->diff($start)->format('%H:%I:%S');

        return $diff;
    }

    public function checkTimeConflict($from, $to, $logs)
    {
        foreach ($logs as $log) {
            $startTime = Carbon::parse($log['start_time']);
            $endTime = Carbon::parse($log['end_time']);
            if ($from->between($startTime, $endTime)) {
                return true;
            }
            if ($to->between($startTime, $endTime)) {
                return true;
            }
        }
        return false;
    }

    public function updateEntries(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'logId' => 'required|integer',
            'timeLogId' => 'required|string',
            'from_datetime' => 'required|date|before:to_datetime',
            'to_datetime' => 'required|date|after:from_datetime',
            'description' => 'required|string',
            'duration' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        try {
            $trackerLog = TrackerLog::find($request->logId);
            if (!$trackerLog) {
                return response()->json(["status" => 404, "message" => "No tracker log found"], 404);
            } else {
                $time_logs = $trackerLog->time_logs;
                if ($time_logs) {
                    $time_logs = json_decode($time_logs, true);
                }
                $timeLogId = $request->timeLogId;
                $time_logs[$timeLogId]['type'] = 'entry';
                $time_logs[$timeLogId]['description'] = $request->description;

                $trackerLog->elapsed_time = $this->addTimes($trackerLog->elapsed_time, $time_logs[$timeLogId]['duration']);
                $trackerLog->time_logs = json_encode($time_logs);
                $trackerLog->save();
            }
            return response()->json(["status" => 200, "message" => "Entry Updated"], 200);
        } catch (Exception $e) {
            Log::error('Error with update log ' . $e);
            return response()->json(['status' => false, 'error' => $e->getMessage()], 500);
        }
    }
    public function updateManualEntries(Request $request)
    {
        // dd($request->all());
        $user = auth()->user();
        if ($user && $user->cannot('edit_user_log')) {
            return response()->json(["status" => 403, 'message' => 'Permission denied.'], 403);
        }
        $validator = Validator::make($request->all(), [
            'logId' => 'required|integer',
            'from_datetime' => 'required|date|before:to_datetime',
            'to_datetime' => 'required|date|after:from_datetime',
            'description' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try {
            $trackerLog = TrackerLog::find($request->logId);
            if (!$trackerLog) {
                return response()->json(["status" => 404, "message" => "No tracker log found"], 404);
            } else {
                $time_logs = $trackerLog->time_logs;
                if ($time_logs) {
                    $time_logs = json_decode($time_logs, true);
                }
                $fromDatetime = Carbon::parse($request->from_datetime);
                $toDatetime = Carbon::parse($request->to_datetime);

                $conflict = $this->checkTimeConflict($fromDatetime, $toDatetime, $time_logs);

                if ($conflict) {
                    return response()->json([
                        'status' => 400,
                        'message' => 'Conflict in Time!'
                    ], 400);
                }

                $duration = $fromDatetime->diff($toDatetime)->format('%H:%I:%S');

                $manualId = "m0";
                while (true) {
                    if (array_key_exists($manualId, $time_logs)) {
                        $subId = substr($manualId, 1);
                        $manualId = "m" . (string) ((int) $subId + 1);
                    } else {
                        $time_logs[$manualId] = [
                            'start_time' => $request->from_datetime,
                            'end_time' => $request->to_datetime,
                            'duration' => $duration,
                            'description' => $request->description,
                            'type' => 'entry',
                        ];
                        break;
                    }
                }

                $trackerLog->elapsed_time = $this->addTimes($trackerLog->elapsed_time, $duration);
                $trackerLog->time_logs = json_encode($time_logs);
                $trackerLog->save();
            }
            return response()->json(["status" => 200, "message" => "Manual Entry Updated"], 200);
        } catch (Exception $e) {
            Log::error('Error with update log ' . $e);
            return response()->json(['status' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function updateBreakEnteries(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'logId' => 'required|integer',
            'timeLogId' => 'required|string',
            'from_datetime' => 'required|date|before:to_datetime',
            'to_datetime' => 'required|date|after:from_datetime',
            'description' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try {
            $trackerLog = TrackerLog::find($request->logId);
            if (!$trackerLog) {
                return response()->json(["status" => 404, "message" => "No tracker log found"], 404);
            } else {
                $time_logs = $trackerLog->time_logs;
                if ($time_logs) {
                    $time_logs = json_decode($time_logs, true);
                }
                $fromDatetime = Carbon::parse($request->from_datetime);
                $toDatetime = Carbon::parse($request->to_datetime);

                $timeLogId = $request->timeLogId;
                if (!array_key_exists($timeLogId, $time_logs)) {
                    return response()->json(["status" => 404, "message" => "No untracked data found"], 404);
                }

                $startTime = Carbon::parse($time_logs[$timeLogId]['start_time']);
                $endTime = Carbon::parse($time_logs[$timeLogId]['end_time']);
                $untrackedDuration = $time_logs[$timeLogId]['duration'];
                
                if ($fromDatetime->between($startTime, $endTime) || $toDatetime->between($startTime, $endTime)) {
                    $duration = $fromDatetime->diff($toDatetime)->format('%H:%I:%S');

                    $manualId = "m0";
                    while (true) {
                        if (array_key_exists($manualId, $time_logs)) {
                            $subId = substr($manualId, 1);
                            $manualId = "m" . (string) ((int) $subId + 1);
                        } else {
                            $time_logs[$manualId] = [
                                'start_time' => $request->from_datetime,
                                'end_time' => $request->to_datetime,
                                'duration' => $duration,
                                'description' => $request->description,
                                'type' => 'entry',
                            ];
                            break;
                        }
                    }

                    $d1 = Carbon::createFromTimeString($untrackedDuration);
                    $d2 = Carbon::createFromTimeString($duration);
                    $timeDiff = $d1->diff($d2)->format('%H:%I:%S');

                    $time_logs[$timeLogId]['duration'] = $timeDiff;
                    $time_logs[$timeLogId]['type'] = 'break';

                    $trackerLog->elapsed_time = $this->addTimes($trackerLog->elapsed_time, $duration);
                    $trackerLog->time_logs = json_encode($time_logs);
                    $trackerLog->save();
                } else {
                    return response()->json([
                        'status' => 400,
                        'message' => 'Time is beyond Break!'
                    ], 400);
                }
            }
            return response()->json(["status" => 200, "message" => "Manual Entry Updated"], 200);
        } catch (Exception $e) {
            Log::error('Error with update log ' . $e);
            return response()->json(['status' => false, 'error' => $e->getMessage()], 500);
        }
    }
}