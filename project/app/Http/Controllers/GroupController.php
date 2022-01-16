<?php

namespace App\Http\Controllers;

use App\Models\Group;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Intervention\Image\ImageManagerStatic as Image;

class GroupController extends Controller {

    public function index()
    {
        return view('groups.index', ['groups' => Group::paginate(5)]);
    }


    public function create()
    {
        return view('groups.create');
    }

    public function store()
    {
        $attributes = request()->validate([
            'name' => 'required',
            'avatar' => 'image',
            'smart_billing' => 'boolean',
        ]);
        if ($attributes['avatar'] ?? false) {
            $image = Image::make(request()->file('avatar'));
            $fileName = 'group_avatars/' . time() . '.' . request()->file('avatar')->getClientOriginalExtension();
            $image->save(storage_path('app/public/' . $fileName));
        } else {
            $fileName = 'group_avatars/default_group.png';
        }

        $attributes['avatar'] = $fileName;
        $attributes['user_id'] = auth()->user()->id;
        $attributes['slug'] = Str::slug($attributes['name']);

        $group = Group::create($attributes);
        $group->users()->attach(auth()->user()->id);
        return redirect(route('groups.index'))->with('success', 'Group has been created');
    }

    public function show(Group $group)
    {
        return view('groups.show', ['group' => $group]);
    }

    public function edit(Group $group)
    {
        return view('groups.edit')->withGroup($group);
    }

    public function update(Group $group)
    {
        $attributes = request()->validate([
            'name' => 'required',
            'avatar' => 'image|nullable',
            'smart_billing' => 'boolean',
        ]);

        if ($attributes['avatar'] != null) {
            $image = Image::make(request()->file('avatar'));
            $fileName = 'group_avatars/' . time() . '.' . request()->file('avatar')->getClientOriginalExtension();
            $image->save(storage_path('app/public/' . $fileName));
            $group->avatar = $fileName;
        }

        $group->slug = Str::slug($attributes['name']);
        $group->name = $attributes['name'];
        //TODO smart_billing trzeba dodac tutaj - NIE TRZEBA

        $group->save();
        return redirect(route('groups.show', $group))->with('success', 'Group has been edited');

    }

    public function destroy(Group $group)
    {
        $group->delete();

        return redirect()->route('groups.index')->with('success', 'Group has been deleted');
    }

}
