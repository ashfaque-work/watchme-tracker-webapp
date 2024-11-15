<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Shift;
use App\Models\User;

class ShiftController extends Controller
{

    public function create()
    {
        $user = auth()->user();
        return view("shift.create", compact("user"));
    }

    public function store(Request $request)
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

        return redirect()->back()->with('success', 'Shift Created');
    }

    public function edit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $shift = Shift::find($request->id);

        if ($shift) {
            return response()->json($shift, 200);
        } else {
            return response()->json(['message' => 'Shift not found'], 404);
        }
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
            'name' => 'required|string|unique:shifts',
            'type' => 'required|string',
            'start_time' => ['required', 'regex:/^(?:[01]\d|2[0-3]):[0-5]\d:[0-5]\d$/'],
            'end_time' => ['required', 'regex:/^(?:[01]\d|2[0-3]):[0-5]\d:[0-5]\d$/'],
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $shift = Shift::find($request->id);

        if ($shift) {
            $shift->update([
                'name' => $request->name,
                'type' => $request->type,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
            ]);
        } else {
            return response()->json(['message' => 'Shift not found'], 404);
        }

        return response()->json($shift, 200);
    }

    public function delete(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:shifts,id',
        ]);

        $shift = Shift::find($request->id);

        if ($shift) {
            $shift->delete();

            return response()->json(['message' => 'Shift deleted successfully'], 200);
        } else {
            return response()->json(['message' => 'Shift not found'], 404);
        }
    }


    //Update shift for users
    public function updateUserShift(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'userId' => 'required|integer',
            'shift_name' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $shiftName = $request->shift_name;
        $shift = Shift::where('name', $shiftName)->first();
        if ($shift) {
            $user = User::find($request->userId);
            $user->shift_id = $shift->id;
            $user->save();
        }

        return redirect()->route('admin.user-list')->with('success', 'Shift updated successfully.');
    }    
}
