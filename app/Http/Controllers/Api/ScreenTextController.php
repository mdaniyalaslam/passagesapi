<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ScreenText\StoreOrUpdateRequest;
use App\Http\Resources\ScreenText\AllScreenTextResource;
use App\Models\ScreenText;
use Error;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class ScreenTextController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $text = ScreenText::first();
            return response()->json([
                'status' => true,
                'message' => ($text->count()) . " text(s) found",
                'data' => new AllScreenTextResource($text),
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
     */
    public function store_or_update(StoreOrUpdateRequest $request)
    {
        try {
            DB::beginTransaction();
            $text = ScreenText::first();
            if (empty($text))
                $text = new ScreenText();
            $text->title1 = $request->title1;
            $text->desc1 = $request->desc1;
            $text->title2 = $request->title2;
            $text->desc2 = $request->desc2;
            $text->title3 = $request->title3;
            $text->desc3 = $request->desc3;
            if (!$text->save())
                throw new Error('Text not update');
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => "Updated Successfully",
                'data' => new AllScreenTextResource($text),
            ]);
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }
}
