<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Gift\StoreRequest;
use App\Http\Requests\Gift\UpdateRequest;
use App\Http\Resources\Gift\AllGiftResource;
use App\Models\Gift;
use App\Models\Log;
use Carbon\Carbon;
use Error;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class GiftController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = Gift::query();
            if (!empty($request->skip))
                $query->skip($request->skip);
            if (!empty($request->take))
                $query->take($request->take);
            if(!empty($request->tab) && $request->tab == "category")
                $query->orderBy('category', 'ASC');
            if(!empty($request->tab) && $request->tab == "a-z")
                $query->orderBy('name', 'ASC');
            if(!empty($request->tab) && $request->tab == "popular")
                $query->orderBy('is_popular', 'DESC');
            $gift = $query->get();
            return response()->json([
                'status' => true,
                'message' => ($gift->count()) . " gift(s) found",
                'data' => AllGiftResource::collection($gift),
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
     * @param  \App\Http\Requests\Gift\StoreRequest  $request
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
                $image->storeAs('gift', $filename, "public");
                $inputs['image'] = "gift/" . $filename;
            }
            $gift = Gift::create($inputs);

            $today_date = Carbon::now();
            $logs = new Log();
            $logs->user_id = auth()->user()->id;
            $logs->title = 'Gift Add';
            $logs->date = $today_date;
            $logs->message = 'New Gift has been successfully added at ' . $today_date;
            if (!$logs->save()) throw new Error('Logs not saved');
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => "Gift has been successfully added.",
                'gift' => new AllGiftResource($gift),
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
     * @param  \App\Models\Gift $gift
     */
    public function show(Gift $gift)
    {
        if (empty($gift)) {
            return response()->json([
                'status' => false,
                'message' => "Gift not found",
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => "Gift has been successfully found",
            'gift' => new AllGiftResource($gift),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     * @param  \App\Http\Requests\Gift\UpdateRequest  $request
     * @param  \App\Models\Gift $gift
     */
    public function update(UpdateRequest $request, Gift $gift)
    {
        if (empty($gift)) {
            return response()->json([
                'status' => false,
                'message' => "Gift not found",
            ], 404);
        }

        try {
            DB::beginTransaction();
            $inputs = $request->except(
                'image',
            );
            if (!empty($request->image)) {
                if (!empty($gift->image) && file_exists(public_path('storage/' . $gift->image))) unlink(public_path('storage/' . $gift->image));
                $image = $request->image;
                $filename = "Image-" . time() . "-" . rand() . "." . $image->getClientOriginalExtension();
                $image->storeAs('gift', $filename, "public");
                $inputs['image'] = "gift/" . $filename;
            }
            $gift->update($inputs);

            $today_date = Carbon::now();
            $logs = new Log();
            $logs->user_id = auth()->user()->id;
            $logs->title = 'Gift Update';
            $logs->date = $today_date;
            $logs->message = 'Gift has been successfully updated at ' . $today_date;
            if (!$logs->save()) throw new Error('Logs not saved');
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => "Gift has been successfully updated",
                'gift' => new AllGiftResource($gift),
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
     * @param  \App\Models\Gift $gift
     */
    public function destroy(Gift $gift)
    {
        if (empty($gift)) {
            return response()->json([
                'status' => false,
                'message' => "Gift not found",
            ], 404);
        }

        try {
            DB::beginTransaction();
            if (!empty($gift->image) && file_exists(public_path('storage/' . $gift->image))) unlink(public_path('storage/' . $gift->image));
            $gift->delete();
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => "Gift has been successfully deleted",
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
