<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Log\AllLogResource;
use App\Models\Log;
use Illuminate\Http\Request;
use Throwable;

class LogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = Log::with('user');
            if (!empty($request->skip))
                $query->skip($request->skip);
            if (!empty($request->take))
                $query->take($request->take);
            $logs = $query->orderBy('id', 'DESC')->get();
            return response()->json([
                'status' => true,
                'message' => ($logs->count()) . " logs(s) found",
                'data' => AllLogResource::collection($logs),
            ]);
        } catch (Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     * @param  \App\Models\Log  $log
     */
    public function show(Log $log)
    {
        if (empty($log)) {
            return response()->json([
                'status' => false,
                'message' => "Log not found",
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => "Log has been successfully found",
            'log' => new AllLogResource($log->load('user')),
        ]);
    }
}
