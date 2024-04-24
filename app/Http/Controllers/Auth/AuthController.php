<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\SimpleEmail;
use App\Models\ForgetOtp;
use App\Models\User;
use App\Models\UserRegisteredDevices;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    use ApiResponser;
    public function loginwithgoogle(Request $req)
    {
        $data = $req->all();
        $user = $data['user'];

        $userEmail = User::where('email', $user['email'])->where('role', 2)->first();


        if ($userEmail != '') {
            $success['token'] =  $userEmail->createToken('MyAuthApp')->plainTextToken;

            return Response(['status' => 'success', 'message' => 'User Login Successfully', 'data' => $success], 200);
        } else {
            $usernew = new User();
            $usernew->role = 2;
            $usernew->name = $user['name'];
            $usernew->email = $user['email'];
            $usernew->auth_id = $user['id'];
            $usernew->image = "images/userimages/dummy-profile.png";
            $usernew->save();
            $success['token'] =  $usernew->createToken('MyApp')->plainTextToken;

            $mail = Mail::to($usernew->email)->send(new SimpleEmail($usernew));

            if (!$mail) {
                return response()->json(['status' => false, 'message' => 'Failed to send email']);
            }
            return Response(['status' => 'Success', 'message' => 'Account Create Successfully', 'data' => $success], 200);
        }
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'string|email|unique:users,email',
            'password' => 'string',
            'user_name' => 'required',
            'confirm_password' => 'required'
        ]);

        //string validation not working 
        if ($validator->fails()) {
            $errors = $validator->errors()->toArray();
            return response()->json(['status' => false, 'errors' => $errors], 400);
        }

        if ($request->password == $request->confirm_password) {

            $user = new User();
            $user->password = $request->password;
            $user->email = $request->email;
            $user->name = $request->user_name;
            if ($request->hasFile('image')) {

                $path = "images/userimages";
                $image = $request->file('image');
                if ($user->image == null) {
                    $user->image = $this->image_upload($image, $path);
                } else {
                    $this->image_delete($user->image);
                    $user->image = $this->image_upload($image, $path);
                }
            }
            $user->image = "images/userimages/dummy-profile.png";
            $user->phone_number = $request->phone_number;
            $user->role = 2;


            if ($user->save()) {

                $mail = Mail::to($user->email)->send(new SimpleEmail($user));

                if (!$mail) {
                    return response()->json(['status' => false, 'message' => 'Failed to send email']);
                }


                return $this->success([
                    'token' => $user->createToken('API Token')->plainTextToken,
                ], "User Registered Success");
            } else {

                return $this->error("Something Went Wrong!", 500);
            }
        } else {
            return $this->error("Password And Confirm Password Didn't Match", 200);
        }
    }

    public function login(Request $request)
    {

        $attr = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);



        $chk_email = User::where('email', $request->email)->first();
           if(!$chk_email)
        {
            return $this->error('User Do not Exits!', 401);
        }
        $count = UserRegisteredDevices::where(['device_id' => $request->device_id, 'user_id' => $chk_email->id])->count();

      
        if (($count > 0 && $chk_email->role == 2) || $chk_email->role == 1) {

            if (!Auth::attempt($attr)) {
                return $this->error('Login credentials do not match', 401);
            }

            $msg = ($chk_email->role == 1) ? "Admin Login Success" : "User Login Success";

            //   dd(auth()->user());
            return response()->json(['status' => true,'token' => $chk_email->createToken('API Token')->plainTextToken,'role' => $chk_email->role,'otp_status'=>true, 'message' => $msg]);

        } else {
            $opt = rand(10000, 99990);
            ForgetOtp::updateOrCreate(
                ['email' => $request->email],
                [
                    'email'     => $request->email,
                    'otp'      => $opt,
                    'device_id'=> $request->device_id
                ]
            );

            $data = [
                'email' => $request->email,
                'user'=>$chk_email->toArray(),
                'details' => [
                    'heading' => 'Verify Device Opt',
                    'content' => 'Your verify device otp : ' . $opt,
                    'WebsiteName' => 'Irving Segal'
                ]

            ];

            $datamail = Mail::send('mail.sendopt', $data, function ($message) use ($data) {
                $message->to($data['email'])->subject($data['details']['heading']);
            });

            if (!$datamail) {
                return response()->json(['status' => false, 'message' => 'Failed to send email']);
            }

            return response()->json(['status' => true,'token' => $chk_email->createToken('API Token')->plainTextToken,'otp_status'=>false, 'message' => "OTP send on your email address"]);
        }
    }

    public function device_otp_verification(Request $request)
    {
        $user_id = User::where('id',auth()->user()->id)->first();
        
        $user = ForgetOtp::where(['email' => $user_id->email, 'otp' => $request->otp])->first();

        if (!isset($user)) {
            return response()->json(['status' => false, 'message' => "Otp is wrong"]);
        }

        $user = User::where('email', $user_id->email)->first();
       
        $msg = ($user->role == 1) ? "Admin Login Success" : "User Login Success";

        $get_otp = ForgetOtp::where(['email' => $user_id->email, 'otp' => $request->otp])->first();
        $device_id = $get_otp->device_id;
        if (!isset($get_otp)) {
            return response()->json(['status' => false, 'message' => "Otp is wrong"]);
        } else {
            $get_otp->delete();
        }

        $userdevice = new UserRegisteredDevices();
        $userdevice->user_id = $user->id;
        $userdevice->device_id = $device_id;
        $userdevice->save();


        return $this->success([
            'token' => $user->createToken('API Token')->plainTextToken,
            'role' => $user->role,
        ], $msg);
    }


    public function logout()
    {
        $user = Auth::user();
        $user->currentAccessToken()->delete();

        return $this->success(null, 'Logout Success');
    }

    public function forgot_password(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required',
        ]);

        if ($validator->fails()) {
            return Response(['status' => false, 'error' => $validator->errors()], 401);
        }

        $opt = rand(10000, 99990);

        // dd($opt);
        // $currentDate = Carbon::now()->format('d-M-Y');

        $check_user = User::where('email', $request->email)->select('id', 'email','name')->first();
        // dd($check_user);
        if (!isset($check_user)) {
            return response()->json(['status' => false, 'message' => "User not found"]);
        }

        ForgetOtp::updateOrCreate(
            ['email' => $request->email],
            [
                'email'     => $request->email,
                'otp'      => $opt
            ]
        );

        $data = [
            'email' => $request->email,
            'user'=>$check_user->toArray(),
            'details' => [
                'heading' => 'Forget Password Opt',
                'content' => 'Your forget password otp : ' . $opt,
                'WebsiteName' => 'Irving Segal'
            ]

        ];
        $datamail = Mail::send('mail.sendopt', $data, function ($message) use ($data) {
            $message->to($data['email'])->subject($data['details']['heading']);
        });

        if (!$datamail) {
            return response()->json(['status' => false, 'message' => 'Failed to send email']);
        }


        return response()->json(['status' => true, 'data' => $check_user, 'message' => "OTP send on your email address"]);
    }
    public function otp_verification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'otp' => 'required',
        ]);

        if ($validator->fails()) {
            return Response(['status' => false, 'error' => $validator->errors()], 401);
        }

        $user = ForgetOtp::where(['email' => $request->email, 'otp' => $request->otp])->first();
        if (!isset($user)) {
            return response()->json(['status' => false, 'message' => "Otp is wrong"]);
        }
        $data['email'] = $user->email;
        $data['code'] = $user->otp;

        return response()->json(['status' => true, 'data' => $data]);
    }

    public function reset_password(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'password' => 'required|confirmed',
        ]);

        if ($validator->fails()) {
            return Response(['status' => false, 'error' => $validator->errors()], 401);
        }

        // dd(uniqid());
        $get_otp = ForgetOtp::where(['email' => $request->email, 'otp' => $request->otp])->first();
        if (!isset($get_otp)) {
            return response()->json(['status' => false, 'message' => "Otp is wrong"]);
        } else {
            $get_otp->delete();
        }
        $user = User::where('email', $request->email)->first();
        $user->password = bcrypt($request['password']);

        if ($user->save()) {
            return response()->json(['status' => true, 'message' => "Password Reset"]);
        }
    }
}
