<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ocassion\StoreRequest;
use App\Http\Requests\Ocassion\UpdateRequest;
use App\Http\Resources\Ocassion\AllOcassionResource;
use App\Models\Log;
use App\Models\Ocassion;
use Carbon\Carbon;
use Error;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class OcassionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = Ocassion::query();
            if (!empty($request->skip))
                $query->skip($request->skip);
            if (!empty($request->take))
                $query->take($request->take);
            $ocassion = $query->orderBy('id', 'DESC')->get();
            return response()->json([
                'status' => true,
                'message' => ($ocassion->count()) . " ocassion(s) found",
                'data' => AllOcassionResource::collection($ocassion),
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
     * @param  \App\Http\Requests\Ocassion\StoreRequest  $request
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
                $image->storeAs('ocassion', $filename, "public");
                $inputs['image'] = "ocassion/" . $filename;
            }
            $ocassion = Ocassion::create($inputs);

            $today_date = Carbon::now();
            $logs = new Log();
            $logs->user_id = auth()->user()->id;
            $logs->title = 'Ocassion Add';
            $logs->date = $today_date;
            $logs->message = 'New Ocassion has been successfully added at ' . $today_date;
            if (!$logs->save()) throw new Error('Logs not saved');
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => "Ocassion has been successfully added.",
                'ocassion' => new AllOcassionResource($ocassion),
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
     * @param  \App\Models\Ocassion $ocassion
     */
    public function show(Ocassion $ocassion)
    {
        if (empty($ocassion)) {
            return response()->json([
                'status' => false,
                'message' => "Ocassion not found",
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => "Ocassion has been successfully found",
            'ocassion' => new AllOcassionResource($ocassion),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     * @param  \App\Http\Requests\Ocassion\UpdateRequest  $request
     * @param  \App\Models\Ocassion $ocassion
     */
    public function update(UpdateRequest $request, Ocassion $ocassion)
    {
        if (empty($ocassion)) {
            return response()->json([
                'status' => false,
                'message' => "Ocassion not found",
            ], 404);
        }

        try {
            DB::beginTransaction();
            $inputs = $request->except(
                'image',
            );
            if (!empty($request->image)) {
                if (!empty($ocassion->image) && file_exists(public_path('storage/' . $ocassion->image))) unlink(public_path('storage/' . $ocassion->image));
                $image = $request->image;
                $filename = "Image-" . time() . "-" . rand() . "." . $image->getClientOriginalExtension();
                $image->storeAs('ocassion', $filename, "public");
                $inputs['image'] = "ocassion/" . $filename;
            }
            $ocassion->update($inputs);

            $today_date = Carbon::now();
            $logs = new Log();
            $logs->user_id = auth()->user()->id;
            $logs->title = 'Ocassion Update';
            $logs->date = $today_date;
            $logs->message = 'Ocassion has been successfully updated at ' . $today_date;
            if (!$logs->save()) throw new Error('Logs not saved');
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => "Ocassion has been successfully updated",
                'ocassion' => new AllOcassionResource($ocassion),
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
     * @param  \App\Models\Ocassion $ocassion
     */
    public function destroy(Ocassion $ocassion)
    {
        if (empty($ocassion)) {
            return response()->json([
                'status' => false,
                'message' => "Ocassion not found",
            ], 404);
        }

        try {
            DB::beginTransaction();
            if (!empty($ocassion->image) && file_exists(public_path('storage/' . $ocassion->image))) unlink(public_path('storage/' . $ocassion->image));
            $ocassion->delete();
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => "Ocassion has been successfully deleted",
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
