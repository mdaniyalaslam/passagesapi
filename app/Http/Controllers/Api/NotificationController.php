<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Notification\AllNotificationResource;
use App\Models\AppNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        try {
            $user_id = auth()->user()->id;
            $query = AppNotification::with(['sender','receiver'])->where('receiver_id', $user_id);
            if (!empty($request->skip))
                $query->skip($request->skip);
            if (!empty($request->take))
                $query->take($request->take);
            $notifications = $query->orderBy('id', 'DESC')->get();
            return response()->json([
                'status' => true,
                'message' => ($notifications->count()) . " Notification(s) found",
                'data' => AllNotificationResource::collection($notifications),
            ]);
        } catch (Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function read()
    {
        try {
            DB::beginTransaction();
            $user_id = auth()->user()->id;
            $notifications = AppNotification::where('receiver_id', $user_id)->where('is_read', 0)->get();
            if (!empty($notifications) && count($notifications) > 0) {
                foreach ($notifications as $notification) {
                    $notification->is_read = 1;
                    $notification->save();
                }
            }
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => "Notification Read Successfully",
            ]);
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function count()
    {
        try {
            $user_id = auth()->user()->id;
            $notifications = AppNotification::where('receiver_id', $user_id)->where('is_read', 0)->count();
            return response()->json([
                'status' => true,
                'message' => "Notification count Successfully",
                'count' => $notifications ?? 0,
            ]);
        } catch (Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
