<?php

namespace App\Http\Controllers;

use App\Models\Books;
use App\Models\Users;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function index() {

        $data = Users::all();

        return response()->json($data);

    }
    public function login(Request $request): JsonResponse
    {

        $data = [
            'user' => $request->input('username'),
            'pass' => $request->input('password'),
            'user_err' => '',
            'pass_err' => '',
        ];

        //Check if username and password match with db
        $user = Users::where('username', $data['user'])->first();


        if($user == null || !Hash::check($request->password, $user->password)) {

            $data['pass_err'] = 'Password or Username is not correct!';

        }

        //Check if user field is empty
        if(empty($data['user'])) {
            $data['user_err'] = 'Please fill out username field!';
        }

        //Check if pass field is empty
        if(empty($data['pass'])) {
            $data['pass_err'] = 'Please fill out password field!';
        }

        //If error check is passed return token json
        if(empty($data['user_err']) && empty($data['pass_err'])) {

            $token = Str::random(60);

            Users::where('id', $user->id)->update(['token' => $token]);

            return response()->json([
                'user_err' => '',
                'pass_err' => '',
                'token' => $token,
                'status' => 200
            ]);

        }

        //If error pass failed return error json
        return response()->json([
            'user_err' => $data['user_err'],
            'pass_err' => $data['pass_err'],
            'status' => 403
        ]);
    }

    public function register(Request $request) : JsonResponse
    {
        $user = htmlspecialchars(trim($request->input('username')));
        $pass = htmlspecialchars(trim($request->input('password')));

//        $err_arr = [
//            'user' => '',
//            'pass' => '',
//        ];

        $data = [
            'user' => $user,
            'pass' => $pass,
            'user_err' => '',
            'pass_err' => '',
        ];

        if(empty($user))
        {
            $data['user_err'] = 'Please fill out username  field!';
        }
        if(empty($pass))
        {
            $data['pass_err'] = 'Please fill out password field!';
        }


        //Set a max length for text fields
        if(strlen($user) > 10)
        {
            $data['user_err'] = "Username can't be more than 10 characters";
        }
        if(strlen($pass) > 30)
        {
            $data['pass_err'] = "Password can't be more than 30 characters";
        }

        if(Users::where('username', '=', $user)->exists())
        {
            $data['user_err'] = "Username already exists!";
        }

        //Check if errors are empty
        if(empty($data['user_err']) || empty($data['pass_err']))
        {
            //Hash password
            $hashed_pass = Hash::make($pass);

            //Final data for db
            $data = [
                'username' => $user,
                'password' => $hashed_pass
            ];

            Users::create($data);

            return response()->json([
                'message' => 'Registered successfully!',
                'status' => 200,
            ]);
        }

        return response()->json([
            'user_err' => $data['user_err'],
            'pass_err' => $data['pass_err'],
            'status' => 403
        ]);
    }

    public function verifyToken(Request $request): JsonResponse
    {
        $token = $request->input('token');

        if(empty($token)) {
            return response()->json([
                'status' => 403
            ]);
        }

        if(Users::where('token', '=', $token)->exists())
        {
            return response()->json([
                'status' => 200
            ]);
        }
        else {
            return response()->json([
                'status' => 403
            ]);
        }
    }

    public function findByToken(Request $request)
    {
        $token = $request->input('token');

        $user = Users::where('token', $token)->first();

        if(!$user) {
            return response()->json([
                'status' => 403
            ]);
        }
        return response()->json([
            'uid' => $user->id,
            'username' => $user->username
        ]);
    }
}
