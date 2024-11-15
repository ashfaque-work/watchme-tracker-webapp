<?php

namespace Database\Seeders;

use App\Models\Shift;
use Illuminate\Database\Seeder;

class ShiftSeeder extends Seeder
{
    public function run(): void
    {
        $shifts = [
            [
                'name' => 'morning',
                'type' => 'WFO',
                'start_time' => "09:00:00",
                'end_time' => "19:00:00",
                'created_by' => 1
            ],
            [
                'name' => 'afternoon',
                'type' => 'WFO',
                'start_time' => "13:00:00",
                'end_time' => "23:00:00",
                'created_by' => 1
            ],
            [
                'name' => 'evening',
                'type' => 'WFO',
                'start_time' => "15:00:00",
                'end_time' => "01:00:00",
                'created_by' => 1
            ],
            [
                'name' => 'night',
                'type' => 'WFO',
                'start_time' => "19:00:00",
                'end_time' => "06:00:00",
                'created_by' => 1
            ],
        ];

        // Insert records into the database
        Shift::insert($shifts);
    }
}
