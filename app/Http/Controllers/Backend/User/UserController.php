<?php

namespace App\Http\Controllers\Backend\User;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\Datatables\Datatables;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:admin')->only('index');
    }

    public function index()
    {
        return view('backend.user.index');
    }

    public function getData()
    {
        $users = User::query();

        return Datatables::of($users)
            ->editColumn('name', function ($user) {
                return
                '<a href="'.route('user.edit', $user->name).'">'.$user->name.'</a>';
            })
            ->editColumn('created_at', function ($user) {
                return [
                    'display'   => '<span title="'.$user->created_at->toDayDateTimeString().'" data-toggle="tooltip" style="cursor: default;">'.$user->created_at->diffForHumans().'</span>',
                    'timestamp' => $user->created_at->timestamp,
                ];
            })
            ->addColumn('action', function ($user) {
                return
                '<div class="btn-group" role="group" aria-label="Basic example">
                    <div class="btn-group" role="group" aria-label="Basic example">
                      <a role="button" class="btn" href="'.route('user.edit', $user->name).'" title="'.__('Details').'" data-toggle="tooltip"><i class="fas fa-user-edit"></i></a>
                      <a role="button" class="btn" href="'.route('user.change-password', $user->name).'" title="'.__('Change Password').'" data-toggle="tooltip"><i class="fas fa-key"></i></a>
                    </div>
                 </div>';
            })
            ->rawColumns(['name', 'created_at.display', 'action'])
            ->toJson();
    }

    public function edit($user)
    {
        if ((Auth::user()->name != $user) && Auth::user()->hasRole('admin') == false) {
            return abort(403);
        }

        $user = User::where('name', $user)->first();

        return view('backend.user.profile', [
            'name'  => $user->name,
            'email' => $user->email,
        ]);
    }

    public function update(Request $request, $user)
    {
        $user = User::where('name', $user)->first();

        $user->email = $request->get('email');

        $validatedData = $request->validate([
            'email' => 'required|string|email|max:255|unique:users',
        ]);

        $user->save();

        return redirect()->back()->with('success', 'Profile updated.');
    }
}