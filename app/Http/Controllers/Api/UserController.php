<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

use function PHPUnit\Framework\isNull;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();
        if (count($users) > 0) {
            $response = [
                'message' => count($users) . ' Users Found',
                'status' => 1,
                'data' => $users
            ];
        } else {
            $response = [
                'message' => count($users) . ' Users Found',
                'status' => 0
            ];
        }
        return response()->json($response, 200);
    }

    public function create()
    {
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'min:8', 'confirmed'],
            'password_confirmation' => ['required']
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 400);
        } else {
            $data = [
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ];
            DB::beginTransaction();
            try {
                $user = User::create($data);
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                $user = null;
            }

            if ($user != null) {
                return response()->json([
                    'message' => 'User Registered Successfully',
                    'date' => $user
                ], 200);
            } else {
                return response()->json(['message' => 'Internal Server Error'], 500);
            }
        }
    }

    public function show($id)
    {
        $user = User::find($id);
        if (is_null($user)) {
            $response = [
                'message' => 'User Not Found',
                'status' => 0
            ];
        } else {
            $response = [
                'message' => 'User Found',
                'status' => 1,
                'data' => $user
            ];
        }
        return response()->json($response, 200);
    }

    public function edit($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        // p($request->all());
        // die;
        $user = User::find($id);
        if (is_null($user)) {
            return response()->json([
                'message' => "User Not Found",
                'status' => 0
            ], 404);
        } else {
            DB::beginTransaction();
            try {
                $user->name = $request['name'];
                $user->email  = $request['email'];
                $user->contact  = $request['contact'];
                $user->pincode  = $request['pincode'];
                $user->address  = $request['address'];
                $user->save();
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                $user = null;
            }
            if (is_null($user)) {
                return response()->json([
                    'message' => "Internal Server Error",
                    'status' => 0,
                    'err_message' => $e->getMessage()
                ], 500);
            } else {
                return response()->json([
                    'message' => "Data Updated Successfull",
                    'status' => 1
                ], 200);
            }
        }
    }

    public function destroy($id)
    {
        $user = User::find($id);
        if (is_null($user)) {
            $response = [
                'message' => "User Does't Exists",
                'status' => 0,
            ];
            $resCode = 404;
        } else {
            DB::beginTransaction();
            try {
                $user->delete();
                DB::commit();
                $response = [
                    'message' => 'User Deleted Successfully!',
                    'status' => 1
                ];
                $resCode = 200;
            } catch (\Exception $e) {
                DB::rollBack();
                $response = [
                    'message' => 'Internal Server Error',
                    'status' => 0
                ];
                $resCode = 500;
            }
        }

        return response()->json($response, $resCode);
    }

    public function changePassword(Request $request, $id)
    {
        // p($request->all());
        // die;
        $user = User::find($id);
        if (is_null($user)) {
            return response()->json([
                'message' => "User Not Found",
                'status' => 0
            ], 404);
        } else {
            if (Hash::check($request['old_password'], $user->password)) {
                if ($request['new_password'] == $request['confirm_password']) {
                    DB::beginTransaction();
                    try {
                        $user->password = Hash::make($request['new_password']);
                        $user->save();
                        DB::commit();

                        return response()->json([
                            'message' => "Password Changed Success",
                            'status' => 1
                        ], 200);
                    } catch (\Exception $e) {
                        DB::rollBack();
                        $user = null;
                    }
                    if (is_null($user)) {
                        return response()->json([
                            'message' => "Internal Server Error",
                            'status' => 0,
                            'err_message' => $e->getMessage()
                        ], 500);
                    } else {
                        return response()->json([
                            'message' => "Password Changed Successful",
                            'status' => 1
                        ], 200);
                    }
                } else {
                    return response()->json([
                        'message' => "Password Confirm Does't Matched",
                        'status' => 0
                    ], 404);
                }
            } else {
                return response()->json([
                    'message' => "Old Password Does't Matched",
                    'status' => 0
                ], 404);
            }
        }
    }
}
