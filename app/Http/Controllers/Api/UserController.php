<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\VerifyRequest;
use App\Http\Requests\User\StoreRequest;
use App\Http\Requests\User\UpdateRequest;
use App\Http\Resources\User\AllUserResource;
use App\Models\User;
use Error;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Throwable;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = User::with('role');
            if (!empty($request->skip))
                $query->skip($request->skip);
            if (!empty($request->take))
                $query->take($request->take);
            $user = $query->orderBy('id', 'DESC')->get();
            return response()->json([
                'status' => true,
                'message' => ($user->count()) . " user(s) found",
                'data' => AllUserResource::collection($user),
            ]);
        } catch (Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     * @param  \App\Http\Requests\User\StoreRequest  $request
     */
    public function store(StoreRequest $request)
    {
        try {
            DB::beginTransaction();
            $token = rand(1000, 9999);
            $inputs = $request->except(
                'role_id','image'
            );
            $inputs['role_id'] = 2;
            $inputs['remember_token'] = $token;
            if (!empty($request->image)) {
                $image = $request->image;
                $filename = "Image-" . time() . "-" . rand() . "." . $image->getClientOriginalExtension();
                $image->storeAs('user', $filename, "public");
                $inputs['image'] = "user/" . $filename;
            }
            $user = User::create($inputs);
            Mail::send('mail.verify', ['user' => $user], function ($message) use ($user) {
                $message->to($user->email, $user->name);
                $message->subject('Registration - Token');
                $message->priority(3);
            });
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => "Register Successfully. Check your email for verification.",
                // 'user' => new AllUserResource($user),
            ]);
        } catch (Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     * @param  \App\Models\User $user
     */
    public function show(User $user)
    {
        if (empty($user) || $user->role_id != 2) {
            return response()->json([
                'status' => false,
                'message' => "User not found",
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => "User has been successfully found",
            'user' => new AllUserResource($user),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     * @param  \App\Http\Requests\User\UpdateRequest  $request
     * @param  \App\Models\User $user
     */
    public function update(UpdateRequest $request, User $user)
    {
        if (empty($user) || $user->role_id != 2) {
            return response()->json([
                'status' => false,
                'message' => "User not found",
            ], 404);
        }

        try {
            DB::beginTransaction();
            $inputs = $request->except(
                'image',
            );
            if (!empty($request->image)) {
                if (!empty($user->image) && file_exists(public_path('storage/' . $user->image)))
                    unlink(public_path('storage/' . $user->image));
                $image = $request->image;
                $filename = "Image-" . time() . "-" . rand() . "." . $image->getClientOriginalExtension();
                $image->storeAs('user', $filename, "public");
                $inputs['image'] = "user/" . $filename;
            }
            $user->update($inputs);
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => "User has been successfully updated",
                'user' => new AllUserResource($user),
            ]);
        } catch (Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param  \App\Models\User $user
     */
    public function destroy(User $user)
    {
        if (empty($user) || $user->role_id != 2) {
            return response()->json([
                'status' => false,
                'message' => "User not found",
            ], 404);
        }

        try {
            DB::beginTransaction();
            if (!empty($user->image) && file_exists(public_path('storage/' . $user->image)))
                unlink(public_path('storage/' . $user->image));
            $user->delete();
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => "User has been successfully deleted",
            ]);
        } catch (Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function verify(VerifyRequest $request)
    {
        try {
            DB::beginTransaction();
            $user = User::where('email', $request->email)->where('remember_token', $request->token)->first();
            if (!$user)
                throw new Error('Invalid email or token.', 422);
            $user->is_active = true;
            $user->remember_token = null;
            $user->save();
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Email verified successfully.',
                'user' => new AllUserResource($user),
            ]);
        } catch (Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function status_change($id)
    {
        try {
            DB::beginTransaction();
            $user = User::where('id', $id)->first();
            if (empty($user))
                throw new Error('User not found');
            if ($user->is_active == 1) {
                $user->is_active = 0;
                if (!$user->save())
                    throw new Error('Account not delete');
                DB::commit();
                return response()->json(['status' => true, 'message' => 'Successfully Delete Your Account']);
            } else {
                $user->is_active = 1;
                if (!$user->save())
                    throw new Error('Account not delete');
                DB::commit();
                return response()->json(['status' => true, 'message' => 'Successfully Recover Your Account']);
            }
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => $th->getMessage()], 500);
        }
    }
    
    public function uploadPhoto(Request $request)
    {
        try {
            $user = User::where('email',$request->email)->first();
            if (empty($user))
                throw new Error('User not found');
            if (!empty($request->image)) {
                if (!empty($user->image) && file_exists(public_path('storage/' . $user->image)))
                    unlink(public_path('storage/' . $user->image));
                $image = $request->image;
                $filename = "Profile-Photo" . time() . "-" . rand() . "." . $image->getClientOriginalExtension();
                $image->storeAs('profile', $filename, "public");
                $user->image = "profile/" . $filename;
            }
            if (!$user->save())
                    throw new Error('Photo Upload Failed');
            return response()->json(['status' => true, 'message' => 'Successfully Upload Photo', 'user' => new AllUserResource($user->load('role'))]);
        } catch (Throwable $th) {
            return response()->json(['status' => false, 'message' => $th->getMessage()], 500);
        }
    }
}
