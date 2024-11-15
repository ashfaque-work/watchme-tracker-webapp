<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Shift;

class ShiftController extends Controller
{
    public function updateUserShift(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|exists:users,email',
            'shift_name' => 'required|string|exists:shifts,name'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::where('email', $request->email)->firstOrFail();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found'
            ]);
        }
        $shift = Shift::where('name', $request->shift_name)->firstOrFail();

        $user->shift_id = $shift->id;
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Shift updated',
            'user' => $user
        ]);
    }

    public function getUserShift(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::where('email', $request->email)->firstOrFail();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found'
            ]);
        }

        if(!$user->shift){
            return response()->json([
                'status'=> 'error',
                'message'=> 'No shift is assigned to this user! Please update shift first.'
            ]);
        }
        
        return response()->json([
            'status'=> 'success',
            'email' => $user->email,
            'shift'=> $user->shift->name,
            'type'=> $user->shift->type,
            'start_time'=> $user->shift->start_time,
            'end_time'=> $user->shift->end_time,
        ]);
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:shifts',
            'type' => 'required|string',
            'start_time' => ['required', 'regex:/^(?:[01]\d|2[0-3]):[0-5]\d:[0-5]\d$/'],
            'end_time' => ['required', 'regex:/^(?:[01]\d|2[0-3]):[0-5]\d:[0-5]\d$/'],
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $shift = Shift::create([
            'name' => $request->name,
            'type' => $request->type,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'created_by' => auth()->user()->id,
        ]);

        return response()->json($shift, 200);
    }
}
