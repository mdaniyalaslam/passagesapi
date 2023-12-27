<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Help\StoreRequest;
use App\Http\Requests\Help\UpdateRequest;
use App\Http\Resources\Help\AllHelpResource;
use App\Models\Help;
use App\Models\Log;
use Carbon\Carbon;
use Error;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class HelpController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = Help::query();
            if (!empty($request->search))
                $query->where('title', $request->search);
            if (!empty($request->skip))
                $query->skip($request->skip);
            if (!empty($request->take))
                $query->take($request->take);
            $help = $query->orderBy('id', 'DESC')->get();
            return response()->json([
                'status' => true,
                'message' => ($help->count()) . " help(s) found",
                'data' => AllHelpResource::collection($help),
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
     * @param  \App\Http\Requests\Help\StoreRequest  $request
     */
    public function store(StoreRequest $request)
    {
        try {
            DB::beginTransaction();
            $help = Help::create($request->validated());

            $today_date = Carbon::now();
            $logs = new Log();
            $logs->user_id = auth()->user()->id;
            $logs->title = 'Help Add';
            $logs->date = $today_date;
            $logs->message = 'New Help has been successfully added at ' . $today_date;
            if (!$logs->save())
                throw new Error('Logs not saved');
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => "Help has been successfully added.",
                'help' => new AllHelpResource($help),
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
     * @param  \App\Models\Help $help
     */
    public function show(Help $help)
    {
        if (empty($help)) {
            return response()->json([
                'status' => false,
                'message' => "Help not found",
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => "Help has been successfully found",
            'help' => new AllHelpResource($help),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     * @param  \App\Http\Requests\Help\UpdateRequest  $request
     * @param  \App\Models\Help $help
     */
    public function update(UpdateRequest $request, Help $help)
    {
        if (empty($help)) {
            return response()->json([
                'status' => false,
                'message' => "Help not found",
            ], 404);
        }

        try {
            DB::beginTransaction();
            $help->update($request->validated());

            $today_date = Carbon::now();
            $logs = new Log();
            $logs->user_id = auth()->user()->id;
            $logs->title = 'Help Update';
            $logs->date = $today_date;
            $logs->message = 'Help has been successfully updated at ' . $today_date;
            if (!$logs->save())
                throw new Error('Logs not saved');
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => "Help has been successfully updated",
                'help' => new AllHelpResource($help),
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
     * @param  \App\Models\Help $help
     */
    public function destroy(Help $help)
    {
        if (empty($help)) {
            return response()->json([
                'status' => false,
                'message' => "Help not found",
            ], 404);
        }

        try {
            DB::beginTransaction();
            $help->delete();
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => "Help has been successfully deleted",
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
