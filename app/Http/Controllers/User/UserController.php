<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\GlobalOptions;
use App\Models\User;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    use ApiResponser;
    public function profile_edit(Request $req)
    {
        $user = User::where('id', auth()->user()->id)->first();
        $user->name = isset($req->user_name) ? $req->user_name : $user->name;
        $user->phone_number = isset($req->phone_number) ? $req->phone_number : $user->phone_number;

        if ($req->hasFile('image')) {

            $path = "images/userimages";
            $image = $req->file('image');
            if ($user->image == null) {
                $user->image = $this->image_upload($image, $path);
            } else {
                if ($user->image != 'images/userimages/dummy-profile.png') {     
                    $this->image_delete($user->image);
                }
                $user->image = $this->image_upload($image, $path);
            }
        }

        $user->save();

        return $this->success($user,'Profile Update Success');
    }

    public function profile_view()
    {
        $data = User::where('id', auth()->user()->id)->first();

        return $this->success($data, "User View Success");
    }
    
    public function logo()
    {
        $data = GlobalOptions::where('id',1)->first();
        return $this->success($data, "Logo Get Success");
    }
}
