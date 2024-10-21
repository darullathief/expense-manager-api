<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function register(Request $request){
        $validate = Validator::make($request->all(),[
            'name' => 'required',
            'username' => 'required',
            'email' => 'required|email',
            'password' => 'required',
            'confirm_password' => 'required|same:password',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'success' => false,
                'message' => "terjadi kesalahan",
                'data' => $validate->errors(),
            ]);
        }

        $data = $request->all();

        try {
            $user = User::create([
                'name' => $data['name'],
                'username' => $data['username'],
                'email' => $data['email'],
                'password' => bcrypt($data['password'])
            ]);

            return response()->json([
                'success' => true,
                'data' => $data
            ], 200);
    
        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => "terjadi kesalahan",
                'data' => $e->getMessage(),
            ]);
        }
    }
}
