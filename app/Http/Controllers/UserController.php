<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function assignRoleToUser(Request $request,$userId)
    {
        $role = 'customer';
        if($request->role){
            $role = $request->role;
        }
        $user = User::find($userId);
        $role = Role::where('name', $role)->first();

        if ($user && $role) {
            $user->assignRole($role);
            return response()->json(['message' => 'Role assigned to user successfully']);
        } else {
            return response()->json(['error' => 'User or role not found.'], 404);
        }
    }
}
