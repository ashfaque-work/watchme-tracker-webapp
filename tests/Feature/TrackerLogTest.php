<?php

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\User;
use App\Models\Shift;
use App\Models\TrackerLog;
use Carbon\Carbon;

class TrackerLogTest extends TestCase
{
    use DatabaseTransactions;
    public $user;
    protected function setUp(): void
    {
        parent::setUp();
        // Create a user and authenticate
        $this->user = User::where("email", "staff@gmail.com")->first();
        $this->actingAs($this->user);
    }

    public function test_update_log_validation_failure()
    {
        $response = $this->json('POST', '/api/v1/tracker-logs/update-log', []);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'user_id',
                'tracked_time',
                'date',
                'start_time',
                'end_time',
                'type'
            ]);
    }
    public function test_update_log_invalid_time_format()
    {
        $currentDateTime = Carbon::now();

        $response = $this->json('POST', '/api/v1/tracker-logs/update-log', [
            'user_id' => $this->user->id,
            'tracked_time' => '25:00:00', // Invalid time format
            'date' => $currentDateTime->format('Y-m-d'),
            'start_time' => $currentDateTime->format('Y-m-d H:i:s'),
            'end_time' => $currentDateTime->addHour()->format('Y-m-d H:i:s'),
            'type' => 'manual'
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['tracked_time']);
    }

    public function test_update_log_start_time_greater_than_end_time()
    {
        $currentDateTime = Carbon::now();
        $startDateTime = $currentDateTime->copy()->addHour();
        $endDateTime = $currentDateTime->copy();

        $request = [
            'user_id' => $this->user->id,
            'tracked_time' => '01:00:00',
            'date' => $currentDateTime->format('Y-m-d'),
            'start_time' => $startDateTime->format('Y-m-d H:i:s'),
            'end_time' => $endDateTime->format('Y-m-d H:i:s'), // End time is before start time
            'type' => 'manual'
        ];

        $response = $this->json('POST', '/api/v1/tracker-logs/update-log', $request);

        $response->assertStatus(400)
            ->assertJson([
                'status' => 400,
                'message' => 'start_time and end_time are wrong'
            ]);
    }

    public function test_update_log_successful_creation()
    {
        $currentDateTime = Carbon::now();

        $response = $this->json('POST', '/api/v1/tracker-logs/update-log', [
            'user_id' => $this->user->id,
            'tracked_time' => '01:00:00',
            'date' => $currentDateTime->format('Y-m-d'),
            'start_time' => $currentDateTime->format('Y-m-d H:i:s'),
            'end_time' => $currentDateTime->addHour()->format('Y-m-d H:i:s'),
            'type' => 'manual'
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'message' => 'Log Created'
            ]);

        $this->assertDatabaseHas('tracker_logs', [
            'user_id' => $this->user->id,
            'date' => $currentDateTime->format('Y-m-d'),
            'status' => 'started'
        ]);
    }

    public function test_update_log_time_conflict()
    {
        $currentDateTime = Carbon::now();
        $startDateTime = $currentDateTime->copy()->subHours(2);
        $endDateTime = $currentDateTime->copy()->subHour();

        TrackerLog::create([
            'user_id' => $this->user->id,
            'date' => $currentDateTime->format('Y-m-d'),
            'status' => 'started',
            'start_time' => $startDateTime->format('Y-m-d H:i:s'),
            'end_time' => $endDateTime->format('Y-m-d H:i:s'),
            'elapsed_time' => '01:00:00',
            'current_log_id' => 'a0',
            'time_logs' => json_encode([
                'a0' => [
                    'start_time' => $startDateTime->format('Y-m-d H:i:s'),
                    'end_time' => $endDateTime->format('Y-m-d H:i:s'),
                    'duration' => '01:00:00',
                    'type' => 'manual',
                ]
            ])
        ]);

        $startDateTime = $currentDateTime->copy()->subHours(2)->addMinutes(30);
        $endDateTime = $currentDateTime->copy()->subHour();
        $response = $this->json('POST', '/api/v1/tracker-logs/update-log', [
            'user_id' => $this->user->id,
            'tracked_time' => '00:30:00',
            'date' => $currentDateTime->format('Y-m-d'),
            'start_time' => $startDateTime->format('Y-m-d H:i:s'),
            'end_time' => $endDateTime->format('Y-m-d H:i:s'),
            'type' => 'manual'
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'status' => 400,
                'message' => 'Conflict in Time!'
            ]);
    }

    public function test_update_log_successful_update()
    {
        $currentDateTime = Carbon::now();
        $startDateTime = $currentDateTime->copy()->subHours(2);
        $endDateTime = $currentDateTime->copy()->subHour();

        TrackerLog::create([
            'user_id' => $this->user->id,
            'date' => $currentDateTime->format('Y-m-d'),
            'status' => 'started',
            'start_time' => $startDateTime->format('Y-m-d H:i:s'),
            'end_time' => $endDateTime->format('Y-m-d H:i:s'),
            'elapsed_time' => '01:00:00',
            'current_log_id' => 'a0',
            'time_logs' => json_encode([
                'a0' => [
                    'start_time' => $startDateTime->format('Y-m-d H:i:s'),
                    'end_time' => $endDateTime->format('Y-m-d H:i:s'),
                    'duration' => '01:00:00',
                    'type' => 'manual',
                ]
            ])
        ]);

        $startDateTime = $currentDateTime->copy()->subHour();
        $endDateTime = $currentDateTime->copy()->addHour();
        $request = [
            'user_id' => $this->user->id,
            'tracked_time' => '01:00:00',
            'date' => $currentDateTime->format('Y-m-d'),
            'start_time' => $startDateTime->format('Y-m-d H:i:s'),
            'end_time' => $endDateTime->format('Y-m-d H:i:s'),
            'type' => 'manual'
        ];
        $response = $this->json('POST', '/api/v1/tracker-logs/update-log', $request);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Log Updated'
            ]);

        $this->assertDatabaseHas('tracker_logs', [
            'user_id' => $this->user->id,
            'date' => $currentDateTime->format('Y-m-d'),
            'status' => 'resumed',
        ]);
    }

    public function testLogUpdateWhenNoTimeConflictAndTimesMatch1()
    {
        $currentDate = Carbon::now()->format('Y-m-d');
        // Create a TrackerLog with existing time log
        $log = TrackerLog::factory()->create([
            'current_log_id' => 'a0',
            'time_logs' => json_encode([
                'a0' => [
                    'start_time' => Carbon::now()->subMinutes(30)->toDateTimeString(),
                    'end_time' => Carbon::now()->subMinutes(10)->toDateTimeString(),
                    'duration' => '00:20:00',
                    'type' => 'manual',
                ]
            ]),
            'elapsed_time' => '00:20:00'
        ]);
        $request = [
            'user_id' => $this->user->id,
            'tracked_time' => '00:10:00',
            'date' => $currentDate,
            'start_time' => Carbon::now()->subMinutes(10)->toDateTimeString(),
            'end_time' => Carbon::now()->toDateTimeString(),
            'type' => 'manual',
        ];

        $response = $this->json('POST', '/api/v1/tracker-logs/update-log', $request);
        // Fetch the updated log
        $log->refresh();

        // Assertions
        $this->assertEquals('a0', $log->current_log_id);
        $this->assertEquals('00:30:00', $log->elapsed_time);
        $this->assertJson($log->time_logs);
        $timeLogs = json_decode($log->time_logs, true);
        $this->assertEquals($request['end_time'], $timeLogs['a0']['end_time']);
        $this->assertEquals('00:30:00', $timeLogs['a0']['duration']);

        // Response assertions
        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);
    }
    public function testLogUpdateWhenNoTimeConflictAndTimesMatch2()
    {
        $currentDate = Carbon::now()->format('Y-m-d');
        // Create a TrackerLog with existing time log
        $log = TrackerLog::factory()->create([
            'current_log_id' => 'a0',
            'time_logs' => json_encode([
                'a0' => [
                    'start_time' => Carbon::now()->subMinutes(10)->toDateTimeString(),
                    'end_time' => Carbon::now()->subMinutes(5)->toDateTimeString(),
                    'duration' => '00:05:00',
                    'type' => 'manual',
                ]
            ]),
            'elapsed_time' => '00:05:00'
        ]);
        $request = [
            'user_id' => $this->user->id,
            'tracked_time' => '00:05:00',
            'date' => $currentDate,
            'start_time' => Carbon::now()->subMinutes(5)->toDateTimeString(),
            'end_time' => Carbon::now()->toDateTimeString(),
            'type' => 'manual',
        ];

        $response = $this->json('POST', '/api/v1/tracker-logs/update-log', $request);
        // Fetch the updated log
        $log->refresh();

        // Assertions
        $this->assertEquals('a0', $log->current_log_id);
        $this->assertEquals('00:10:00', $log->elapsed_time);
        $this->assertJson($log->time_logs);
        $timeLogs = json_decode($log->time_logs, true);
        $this->assertEquals($request['end_time'], $timeLogs['a0']['end_time']);
        $this->assertEquals('00:10:00', $timeLogs['a0']['duration']);

        // Response assertions
        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);
    }

    public function testLogEnteriesUpdateWithBreakWhenNoTimeConflict1()
    {
        $currentDate = Carbon::now()->format('Y-m-d');
        // Create a TrackerLog with existing time log
        $log = TrackerLog::factory()->create([
            'current_log_id' => 'a0',
            'time_logs' => json_encode([
                'a0' => [
                    'start_time' => Carbon::now()->subMinutes(30)->toDateTimeString(),
                    'end_time' => Carbon::now()->subMinutes(10)->toDateTimeString(),
                    'duration' => '00:20:00',
                    'type' => 'manual',
                ]
            ]),
            'elapsed_time' => '00:20:00'
        ]);
        $request = [
            'user_id' => $this->user->id,
            'tracked_time' => '00:04:00',
            'date' => $currentDate,
            'start_time' => Carbon::now()->subMinutes(4)->toDateTimeString(),
            'end_time' => Carbon::now()->toDateTimeString(),
            'type' => 'manual',
        ];

        $response = $this->json('POST', '/api/v1/tracker-logs/update-log', $request);

        // Fetch the updated log
        $log->refresh();

        // Assertions
        $this->assertEquals('a2', $log->current_log_id);
        $this->assertEquals('00:24:00', $log->elapsed_time);
        $this->assertJson($log->time_logs);
        $timeLogs = json_decode($log->time_logs, true);
        $this->assertEquals($request['start_time'], $timeLogs['a2']['start_time']);
        $this->assertEquals($request['end_time'], $timeLogs['a2']['end_time']);
        $this->assertEquals('untracked', $timeLogs['a1']['type']); //break
        $this->assertEquals('00:06:00', $timeLogs['a1']['duration']); //break
        $this->assertEquals('00:04:00', $timeLogs['a2']['duration']);

        // Response assertions
        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);
    }

    public function testLogEnteriesUpdateWithBreakWhenNoTimeConflict2()
    {
        $currentDate = Carbon::now()->format('Y-m-d');
        // Create a TrackerLog with existing time log
        $log = TrackerLog::factory()->create([
            'current_log_id' => 'a0',
            'time_logs' => json_encode([
                'a0' => [
                    'start_time' => Carbon::now()->subMinutes(40)->toDateTimeString(),
                    'end_time' => Carbon::now()->subMinutes(20)->toDateTimeString(),
                    'duration' => '00:20:00',
                    'type' => 'manual',
                ]
            ]),
            'elapsed_time' => '00:20:00'
        ]);
        $request = [
            'user_id' => $this->user->id,
            'tracked_time' => '00:05:00',
            'date' => $currentDate,
            'start_time' => Carbon::now()->subMinutes(5)->toDateTimeString(),
            'end_time' => Carbon::now()->toDateTimeString(),
            'type' => 'manual',
        ];

        $response = $this->json('POST', '/api/v1/tracker-logs/update-log', $request);

        // Fetch the updated log
        $log->refresh();

        // Assertions
        $this->assertEquals('a2', $log->current_log_id);
        $this->assertEquals('00:25:00', $log->elapsed_time);
        $this->assertJson($log->time_logs);
        $timeLogs = json_decode($log->time_logs, true);
        $this->assertEquals($request['start_time'], $timeLogs['a2']['start_time']);
        $this->assertEquals($request['end_time'], $timeLogs['a2']['end_time']);
        $this->assertEquals('untracked', $timeLogs['a1']['type']); //break
        $this->assertEquals('00:15:00', $timeLogs['a1']['duration']); //break
        $this->assertEquals('00:05:00', $timeLogs['a2']['duration']);

        // Response assertions
        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);
    }
}