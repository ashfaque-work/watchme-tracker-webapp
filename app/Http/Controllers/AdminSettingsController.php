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
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Redirect;

class AdminSettingsController extends Controller
{
    public function index()
    {
        $roles = Role::all();
        $hrRole = $roles->where('name', 'hr')->first();

        $hasEditUserLogPermission = false;
        if ($hrRole) {
            $roleName = $hrRole->name;
            $permissions = $hrRole->permissions;
            $hasEditUserLogPermission = $permissions->contains('name', 'edit_user_log');
        }
        return view('admin.settings', compact('roleName', 'hasEditUserLogPermission'));
    }

    public function updateHrPermission(Request $request)
    {
        $hrRole = Role::where('name', 'hr')->first();
        if ($hrRole) {  
            if ($request && $request->edit_user_log == 'enable') {
                $editUserLogPermission = Permission::where('name', 'edit_user_log')->first();

                $hrRole->givePermissionTo($editUserLogPermission);

            } else if ($request && $request->edit_user_log == 'disable') {
                $editUserLogPermission = Permission::where('name', 'edit_user_log')->first();

                $hrRole->revokePermissionTo($editUserLogPermission);
            }
            return Redirect::back()->with('success', 'Settings updated successfully.');
        }
    }

}