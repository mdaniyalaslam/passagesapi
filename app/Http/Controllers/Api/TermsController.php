<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TermCondition\StoreorUpdateRequest;
use App\Http\Resources\TermCondition\AllTermResource;
use App\Models\TermCondition;
use Error;
use Illuminate\Support\Facades\DB;
use Throwable;

class TermsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $term = TermCondition::first();
            return response()->json([
                'status' => true,
                'message' => (($term) ? $term->count() : 0) . " term(s) found",
                'data' => new AllTermResource($term),
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
     * @param  \App\Http\Requests\TermCondition\StoreorUpdateRequest  $request
     */
    public function store_or_update(StoreorUpdateRequest $request)
    {
        try {
            DB::beginTransaction();
            $term = TermCondition::firstOrNew();
            $term->content = $request->content;
            if (!$term->save())
                throw new Error('Term Condition not update');
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => "Term Condition has been successfully updated.",
                'term' => new AllTermResource($term),
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

