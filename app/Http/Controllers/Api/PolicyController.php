<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Policy\StoreRequest;
use App\Http\Resources\Policy\AllPolicyResource;
use App\Models\Log;
use App\Models\Policy;
use Carbon\Carbon;
use Error;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class PolicyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $policy = Policy::first();
            return response()->json([
                'status' => true,
                'message' => (($policy) ? $policy->count() : 0) . " policy(s) found",
                'data' => new AllPolicyResource($policy),
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
     * @param  \App\Http\Requests\Policy\StoreRequest  $request
     */
    public function store_or_update(StoreRequest $request)
    {
        try {
            DB::beginTransaction();
            $policy = Policy::firstOrNew();
            $policy->content = $request->content;
            if (!$policy->save())
                throw new Error('Privacy Policy not update');
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => "Privacy Policy has been successfully updated.",
                'policy' => new AllPolicyResource($policy),
            ]);
        } catch (Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }
}

