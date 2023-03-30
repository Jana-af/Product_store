<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;


class AuthController extends Controller
{
    public function register(Request $request)
    {
        $rule = [
            'name' => ['required','string','max:255'],
            'email' => ['required','string','email','max:255',Rule::Unique('Users')],
            'password' => ['required', 'string','min:8'],
            'c_password' => ['required', 'string','same:password'],
            'phone' => ['required', 'string','min:9', 'max:10',Rule::unique('users')],
        ];
    

        $input['name'] = $request->input('name');
        $input['email'] = $request->input('email');
        $input['password'] = $request->input('password');
        $input['c_password'] = $request->input('c_password');
        $input['phone'] = $request->input('phone');
        
       $validator = Validator::make($input, $rule);
       if($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->all()
            ], 422);
        }
        $request['password'] = Hash::make($request['password']);
       
        $image_name = 'default.png';

        if ($request->hasFile('image')) {
            $destination_path = 'public/images/users';
            $image = $request->file('image');

            $image_name = implode('.', [
                md5_file($image->getPathname()),
                $image->getClientOriginalExtension()
            ]);

            $path = $request->file('image')->storeAs($destination_path, $image_name);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'phone' => $request->phone,
            'photo' =>  $image_name,
        ]);

        $tokenResult = $user->createToken('jana963')->accessToken;

        return response()->json([
            "success" => true,
            "user" => $user,
            "token" => $tokenResult,
            "message" => 'User Rigester Successfuly !',
        ], 200);

    }

    public function login(Request $request)
    {
        $rule = [
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8'],
        ];

        $input['email'] = $request->input('email');
        $input['password'] = $request->input('password');

        $validator = Validator::make($input, $rule);

        if($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->all()
            ], 422);
        }

        $credentials = request(['email', 'password']);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $tokenResult = $user->createToken('jana963')->accessToken;
           
            return response()->json([
                'success' => true,
                'token' => $tokenResult,
                'user' => $user,
                'message' => 'User logedin Successfuly !',
            ], 200);
           
        }

       else{
            return response()->json([
                'success' => false,
                'data' => $credentials,
                'message' => 'UnAuthorised Access'
            ], 401);
        }

    }

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();

        return response()->json([
            'success' => true,
            'message' => 'logged out successfully !'
        ],200);
    }

    public function userDetails()
    {
        $user = auth()->user();

        return response()->json([
            'success' => true,
            'user' => $user,
        ], 200);
    }
}
