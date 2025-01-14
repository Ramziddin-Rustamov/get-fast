<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DriverController extends Controller
{
  public function login(Request $request)
  {
      $request->validate([
          'password' => 'required|string',
          'phone' => 'required|string',
      ]);
      $credentials = $request->only('phone', 'password');

      $token = Auth::guard('driver')->attempt($credentials);

      if (!$token) {
          return response()->json([
              'status' => 'error',
              'message' => 'Unauthorized',
          ], 401);
      }

      $admin = Auth::guard('admin')->user();
      if(!$this->registerDevice($request, 'login')){
          return  response()->json(['message' => 'User not authenticated.'], 401);
      }
      $userAgent = $request->header('User-Agent');

      return response()->json([
              'status' => 'success',
              'user' => $admin,
              'authorisation' => [
                  'token' => $token,
                  'type' => 'bearer',
              ]
          ]);

  }
  public function register(Request $request){
      $request->validate([
          'first_name' => 'required|string',
          'last_name' => 'required|string',
          'password' => 'required|string',
          'phone' => 'required|string|unique:admins,phone',
          'role' => 'required|string|in:marketolog,operator,administrator,root',
      ]);       

      $admin = Admin::create([
          'country_id' => $request->country_id,
          'role' => $request->role,
          'phone' => $request->phone, 
          'gander' => $request->gender,
          'first_name' => $request->first_name,
          'last_name' => $request->last_name,
          'password' => Hash::make($request->password),
          'added' => now(),
      ]);

      $token = Auth::guard('admin')->login($admin);
      return response()->json([
          'status' => 'success',
          'message' => 'User created successfully',
          'user' => $admin,
          'authorisation' => [
              'token' => $token,
              'type' => 'bearer',
          ]
      ]);
  }
    

  public function logout()
  {
      Auth::guard('admin')->logout();
      return response()->json([
          'status' => 'success',
          'message' => 'Successfully logged out',
      ]);
  }

  
  public function refresh()
  {
      return response()->json([
          'status' => 'success',
          'user' => Auth::guard('admin')->user(),
          'authorisation' => [
              'token' => Auth::guard('admin')->refresh(),
              'type' => 'bearer',
          ]
      ]);
  }

}
