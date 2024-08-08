<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\FileUploadTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserProfileController extends Controller
{
    use FileUploadTrait;
    public function update(Request $request){
        $user = Auth::user();
        
        $request->validate([
            'name' => ['nullable', 'string', 'max:500'],
            'name' => ['required', 'string', 'max:50'],
            'username' => ['required', 'string', 'max:50'],
            'email' => ['required', 'string', 'max:100'],
        ]);

        if($request->filled('current_password')){
            $request->validate([
                'current_password' => ['required'],
                'password' => ['required', 'string', 'min:8', 'confirmed']
            ]);

            $user->password = Hash::make($request->password);
        }

        $avatarPath = $this->uploadFile($request, "avatar");

        if($avatarPath){
            $user->avatar = $avatarPath;
        }

        $user->name = $request->name;
        $user->username = $request->username;
        $user->email = $request->email;
        $user->save();

        return response(["message", "Updated Successfully"], 200);


    }
}
