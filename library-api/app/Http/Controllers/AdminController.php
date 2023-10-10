<?php

namespace App\Http\Controllers;

use App\Models\Admins;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    function login(Request $request): JsonResponse
    {
        //Init data
        $data = [
            'user' => $request->input('username'),
            'pass' => $request->input('password'),
            'userErr' => '',
            'passErr' => '',
        ];

        //Check if username and password match with db
        $user = Admins::where('username', $request->input('username'))->first();

        if(!$user || !Hash::check($request->password, $user->password)) {

            $data['passErr'] = 'Password or Username is not correct!';

        }

        //Check if user field is empty
        if(empty($data['user'])) {
            $data['userErr'] = 'Please fill out username field!';
        }

        //Check if pass field is empty
        if(empty($data['pass'])) {
            $data['passErr'] = 'Please fill out password field!';
        }

        //If error check is passed return token json
        if(empty($data['userErr']) && empty($data['passErr'])) {

            return response()->json([
                'userErr' => '',
                'passErr' => '',
                'token' => $user['token'],
                'status' => 200
            ]);

        }

        //If error pass failed return error json
        return response()->json([
            'userErr' => $data['userErr'],
            'passErr' => $data['passErr'],
            'status' => 403
        ]);

    }

    function routerAuth(Request $request): JsonResponse
    {

        //Math the localStorage token to db token
        $token = Admins::where('token', $request->token)->first();

        //If it returns true, send response auth as true
        if($token) {
            return response()->json([
                'status' => 200
            ]);
        }

        //If it doesn't match, send response auth as false
        return response()->json([
            'status' => 403
        ]);

    }

    function register () {
        $data = [
            'username' => 'admin',
            'password' => '$2a$12$Gk3NqlK0P5WbobJtYE3qWeTYuigag6VLku4TPopFjdsNOr/ElNzjS',
            'token' => 'j7hYinIDjvT0EcnAnSelibk5n1WLvyQhxIWhgXffb3sVUxDyVbTSuUDVsPB4'
        ];

        Admins::create($data);
    }
}
