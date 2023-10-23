<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tone\StoreRequest;
use App\Http\Requests\Tone\UpdateRequest;
use App\Http\Resources\Tone\AllToneResource;
use App\Models\Log;
use App\Models\Tone;
use Carbon\Carbon;
use Error;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class ToneController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = Tone::query();
            if (!empty($request->skip))
                $query->skip($request->skip);
            if (!empty($request->take))
                $query->take($request->take);
            $tone = $query->orderBy('id', 'DESC')->get();
            return response()->json([
                'status' => true,
                'message' => ($tone->count()) . " tone(s) found",
                'data' => AllToneResource::collection($tone),
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
     * @param  \App\Http\Requests\Tone\StoreRequest  $request
     */
    public function store(StoreRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->except(
                'image',
            );
            if (!empty($request->image)) {
                $image = $request->image;
                $filename = "Image-" . time() . "-" . rand() . "." . $image->getClientOriginalExtension();
                $image->storeAs('tone', $filename, "public");
                $inputs['image'] = "tone/" . $filename;
            }
            $tone = Tone::create($inputs);

            $today_date = Carbon::now();
            $logs = new Log();
            $logs->user_id = auth()->user()->id;
            $logs->title = 'Tone Add';
            $logs->date = $today_date;
            $logs->message = 'New Tone has been successfully added at ' . $today_date;
            if (!$logs->save()) throw new Error('Logs not saved');
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => "Tone has been successfully added.",
                'tone' => new AllToneResource($tone),
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
     * @param  \App\Models\Tone $tone
     */
    public function show(Tone $tone)
    {
        if (empty($tone)) {
            return response()->json([
                'status' => false,
                'message' => "Tone not found",
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => "Tone has been successfully found",
            'tone' => new AllToneResource($tone),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     * @param  \App\Http\Requests\Tone\UpdateRequest  $request
     * @param  \App\Models\Tone $tone
     */
    public function update(UpdateRequest $request, Tone $tone)
    {
        if (empty($tone)) {
            return response()->json([
                'status' => false,
                'message' => "Tone not found",
            ], 404);
        }

        try {
            DB::beginTransaction();
            $inputs = $request->except(
                'image',
            );
            if (!empty($request->image)) {
                if (!empty($tone->image) && file_exists(public_path('storage/' . $tone->image))) unlink(public_path('storage/' . $tone->image));
                $image = $request->image;
                $filename = "Image-" . time() . "-" . rand() . "." . $image->getClientOriginalExtension();
                $image->storeAs('tone', $filename, "public");
                $inputs['image'] = "tone/" . $filename;
            }
            $tone->update($inputs);

            $today_date = Carbon::now();
            $logs = new Log();
            $logs->user_id = auth()->user()->id;
            $logs->title = 'Tone Update';
            $logs->date = $today_date;
            $logs->message = 'Tone has been successfully updated at ' . $today_date;
            if (!$logs->save()) throw new Error('Logs not saved');
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => "Tone has been successfully updated",
                'tone' => new AllToneResource($tone),
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
     * @param  \App\Models\Tone $tone
     */
    public function destroy(Tone $tone)
    {
        if (empty($tone)) {
            return response()->json([
                'status' => false,
                'message' => "Tone not found",
            ], 404);
        }

        try {
            DB::beginTransaction();
            if (!empty($tone->image) && file_exists(public_path('storage/' . $tone->image))) unlink(public_path('storage/' . $tone->image));
            $tone->delete();
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => "Tone has been successfully deleted",
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
