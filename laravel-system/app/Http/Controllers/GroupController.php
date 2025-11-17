<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Group;
use App\Models\MobileUser;

class GroupController extends Controller
{
    public function index()
    {
        $groups = Group::with('users')->orderBy('created_at', 'desc')->get();
        $users = MobileUser::orderBy('first_name')->get();

        return view('groups.index', compact('groups', 'users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'group_name' => 'required|string|max:255',
        ]);

        Group::create($request->only('group_name'));

        return back()->with('success', 'Group created successfully!');
    }

    public function addMember(Request $request)
    {
        $request->validate([
            'group_id' => 'required|exists:groups,id',
            'user_phone' => 'required|string',
        ]);

        $user = MobileUser::where('phone', $request->user_phone)->first();

        if (!$user) {
            return back()->withErrors(['error' => 'User not found. Please verify the user first.']);
        }

        $group = Group::find($request->group_id);

        if ($group->users()->where('user_id', $user->id)->exists()) {
            return back()->withErrors(['error' => 'User is already a member of this group.']);
        }

        $group->users()->attach($user->id);

        return back()->with('success', 'Member added to group successfully!');
    }
}
