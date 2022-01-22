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


    public function store(Request $request)
    {
        $attributes = request()->validate([
            'name' => 'required',
            'avatar' => 'image|mimes:jpg,png|max:2048',
            'smart_billing' => 'boolean',
        ]);

        if (!empty(request()->avatar)){
            $fileExtension = request()->file('avatar')->getClientOriginalExtension();
            $fileNameToStore = time().'.'.$fileExtension;
            request()->avatar->move(storage_path('app/public/group_avatars/'),$fileNameToStore);
            $imagePath = 'storage/group_avatars/'.$fileNameToStore;
        }else{
            $imagePath = '/images/default_group.png';
        }

        $group = Group::create([
            'name' => request()->name,
            'user_id' => auth()->user()->id,
            'slug' => Str::slug($attributes['name']),
            'avatar' => $imagePath
        ]);


        $group->users()->attach(auth()->user()->id);
        return redirect(route('groups.index'))->with('success', 'Group has been created');
    }

    public function show(Group $group)
    {

        $expenses_history= Group::find($group->id)->expenses_history;
        return view('groups.show', ['group' => $group,'expenses_history' =>$expenses_history]);
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
            $group->avatar = 'storage/' . $fileName;
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
