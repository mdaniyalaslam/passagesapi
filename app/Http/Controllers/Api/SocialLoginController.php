<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SocialLogin\AppleRequest;
use App\Http\Requests\SocialLogin\FacebookRequest;
use App\Http\Requests\SocialLogin\GoogleRequest;
use App\Http\Resources\Auth\LoginResource;
use App\Models\User;
use Error;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class SocialLoginController extends Controller
{
    protected function googleLogin(GoogleRequest $googleRequest)
    {
        try {
            DB::beginTransaction();
            $role_id = 2;
            $existingAccount = User::where('account_type', 'google')->where('account_id', $googleRequest->id)->where('role_id', $role_id)->first();
            if ($existingAccount != null) {
                if (!auth()->loginUsingId($existingAccount->id))
                    throw new Error('Invalid Credentials!', 412);
                $user = auth()->user()->load('role');
                $user->device_token = $googleRequest->token;
                if (isset($googleRequest->photo)) {
                    $url = $googleRequest->photo;
                    $contents = file_get_contents($url);
                    $name = 'user/Image-' . time() . "-" . rand() . ".png";
                    Storage::disk('public')->put($name, $contents);
                    $user->image = $name;
                }
                if (!$user->save())
                    throw new Error('Something went wrong');
                DB::commit();
                return new LoginResource(['token' => $user->createToken($user->email)->accessToken, 'user' => $user]);
            }

            $socailLogin = new User();
            $socailLogin->role_id = $role_id;
            $socailLogin->account_type = 'google';
            $socailLogin->email = $googleRequest->email;
            $socailLogin->account_id = $googleRequest->id;
            $socailLogin->full_name = $googleRequest->givenName;
            $socailLogin->family_name = $googleRequest->familyName;
            $socailLogin->given_name = $googleRequest->givenName;
            $socailLogin->is_active = true;
            if (isset($googleRequest->phone))
                $socailLogin->phone = $googleRequest->phone;
            if (isset($googleRequest->photo)) {
                $url = $googleRequest->photo;
                $contents = file_get_contents($url);
                $name = 'user/Image-' . time() . "-" . rand() . ".png";
                Storage::disk('public')->put($name, $contents);
                $socailLogin->image = $name;
            }
            if (!$socailLogin->save())
                throw new Error('Something went wrong');
            if (!auth()->loginUsingId($socailLogin->id))
                throw new Error('Invalid Credentials!', 412);
            $user = auth()->user()->load('role');
            $user->device_token = $googleRequest->token;
            $user->save();
            DB::commit();
            return new LoginResource(['token' => $user->createToken($user->email)->accessToken, 'user' => $user]);
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => $th->getMessage()], 500);
        }
    }

    protected function facebookLogin(FacebookRequest $facebookRequest)
    {
        try {
            DB::beginTransaction();
            $role_id = 2;
            $existingAccount = User::where('account_type', 'facebook')->where('account_id', $facebookRequest->id)->where('role_id', $role_id)->first();
            if ($existingAccount != null) {
                if (!auth()->loginUsingId($existingAccount->id))
                    throw new Error('Invalid Credentials!', 412);
                $user = auth()->user()->load('role');
                $user->device_token = $facebookRequest->token;
                if (isset($facebookRequest->photo)) {
                    $url = $facebookRequest->photo;
                    $contents = file_get_contents($url);
                    $name = 'user/Image-' . time() . "-" . rand() . ".png";
                    Storage::disk('public')->put($name, $contents);
                    $user->image = $name;
                }
                if (!$user->save())
                    throw new Error('Something went wrong');
                DB::commit();
                return new LoginResource(['token' => $user->createToken($user->email)->accessToken, 'user' => $user]);
            }

            $socailLogin = new User();
            $socailLogin->role_id = $role_id;
            $socailLogin->account_type = 'facebook';
            $socailLogin->email = $facebookRequest->email;
            $socailLogin->account_id = $facebookRequest->id;
            $socailLogin->full_name = $facebookRequest->givenName;
            $socailLogin->family_name = $facebookRequest->familyName;
            $socailLogin->given_name = $facebookRequest->givenName;
            $socailLogin->is_active = true;
            if (isset($facebookRequest->phone))
                $socailLogin->phone = $facebookRequest->phone;
            if (isset($facebookRequest->photo)) {
                $url = $facebookRequest->photo;
                $contents = file_get_contents($url);
                $name = 'user/Image-' . time() . "-" . rand() . ".png";
                Storage::disk('public')->put($name, $contents);
                $socailLogin->image = $name;
            }
            if (!$socailLogin->save())
                throw new Error('Something went wrong');
            if (!auth()->loginUsingId($socailLogin->id))
                throw new Error('Invalid Credentials!', 412);
            $user = auth()->user()->load('role');
            $user->device_token = $facebookRequest->token;
            $user->save();
            DB::commit();
            return new LoginResource(['token' => $user->createToken($user->email)->accessToken, 'user' => $user]);
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => $th->getMessage()], 500);
        }
    }

    protected function appleLogin(AppleRequest $appleRequest)
    {
        try {
            DB::beginTransaction();
            $role_id = 2;
            $existingAccount = User::where('account_type', 'facebook')->where('account_id', $appleRequest->id)->where('role_id', $role_id)->first();
            if ($existingAccount != null) {
                if (!auth()->loginUsingId($existingAccount->id))
                    throw new Error('Invalid Credentials!', 412);
                $user = auth()->user()->load('role');
                $user->device_token = $appleRequest->token;
                if (isset($appleRequest->photo)) {
                    $url = $appleRequest->photo;
                    $contents = file_get_contents($url);
                    $name = 'user/Image-' . time() . "-" . rand() . ".png";
                    Storage::disk('public')->put($name, $contents);
                    $user->image = $name;
                }
                if (!$user->save())
                    throw new Error('Something went wrong');
                DB::commit();
                return new LoginResource(['token' => $user->createToken($user->email)->accessToken, 'user' => $user]);
            }

            $socailLogin = new User();
            $socailLogin->role_id = $role_id;
            $socailLogin->account_type = 'apple';
            $socailLogin->email = $appleRequest->email;
            $socailLogin->account_id = $appleRequest->id;
            $socailLogin->full_name = $appleRequest->givenName;
            $socailLogin->family_name = $appleRequest->familyName;
            $socailLogin->given_name = $appleRequest->givenName;
            $socailLogin->is_active = true;
            if (isset($appleRequest->phone))
                $socailLogin->phone = $appleRequest->phone;
            if (isset($appleRequest->photo)) {
                $url = $appleRequest->photo;
                $contents = file_get_contents($url);
                $name = 'user/Image-' . time() . "-" . rand() . ".png";
                Storage::disk('public')->put($name, $contents);
                $socailLogin->image = $name;
            }
            if (!$socailLogin->save())
                throw new Error('Something went wrong');
            if (!auth()->loginUsingId($socailLogin->id))
                throw new Error('Invalid Credentials!', 412);
            $user = auth()->user()->load('role');
            $user->device_token = $appleRequest->token;
            $user->save();
            DB::commit();
            return new LoginResource(['token' => $user->createToken($user->email)->accessToken, 'user' => $user]);
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => $th->getMessage()], 500);
        }
    }
}
