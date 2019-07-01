<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;

class ProfileController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('myaccount');
    }

    public function edit(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|min:3|max:200|string|alpha_spaces',
            'email' => 'required|email|max:255',
        ]);

        $user = User::find(Auth::user()->id);
        $user->name = $validatedData['name'];
        $user->email = $validatedData['email'];
        $success = $user->save();

        if (!$success){
            return Redirect::back()->with('info', 'Failed to edit profile.');
        }
        return Redirect::back()->with('info', 'Profile edited successfully.');
    }

    public function changePassword(Request $request)
    {
        $validatedData = $request->validate([
            'currentPassword' => sprintf('required|old_password:%s', Auth::user()->password),
            'newPassword' => 'required|confirmed|min:5|max:255',
        ]);

        $user = User::find(Auth::user()->id);
        $user->password = Hash::make($validatedData['newPassword']);
        $success = $user->save();

        if (!$success) {
            return Redirect::back()->with('info', 'Failed to change user password.');
        }
        return Redirect::back()->with('info', 'User password updated.');
    }
}
