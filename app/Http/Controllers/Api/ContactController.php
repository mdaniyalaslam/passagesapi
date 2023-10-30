<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Contact\StoreRequest;
use App\Http\Requests\Contact\UpdateRequest;
use App\Http\Resources\Contact\AllContactResource;
use App\Models\Contact;
use App\Models\Log;
use App\Models\User;
use Carbon\Carbon;
use Error;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class ContactController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = Contact::with('user');
            if (!empty($request->user_id))
                $query->where('user_id', $request->user_id);
            if (!empty($request->skip))
                $query->skip($request->skip);
            if (!empty($request->take))
                $query->take($request->take);
            $contact = $query->orderBy('id', 'DESC')->get();
            return response()->json([
                'status' => true,
                'message' => ($contact->count()) . " contact(s) found",
                'data' => AllContactResource::collection($contact),
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
     * @param  \App\Http\Requests\Contact\StoreRequest  $request
     */
    public function store(StoreRequest $request)
    {
        try {
            DB::beginTransaction();
            $user = User::where('email', $request->email)->where('is_active', 1)->first();
            if (empty($user))
                throw new Error('First tell the person to register on this app and then you can add them');
            $inputs = $request->except(
                'user_id',
                'image',
            );
            $inputs['user_id'] = auth()->user()->id;
            if (!empty($request->image)) {
                $image = $request->image;
                $filename = "Image-" . time() . "-" . rand() . "." . $image->getClientOriginalExtension();
                $image->storeAs('contact', $filename, "public");
                $inputs['image'] = "contact/" . $filename;
            }
            $contact = Contact::create($inputs);
            $today_date = Carbon::now();
            $logs = new Log();
            $logs->user_id = auth()->user()->id;
            $logs->title = 'Contact Add';
            $logs->date = $today_date;
            $logs->message = 'New Contact has been successfully added at ' . $today_date;
            if (!$logs->save())
                throw new Error('Logs not saved');
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => "Contact has been successfully added.",
                'contact' => new AllContactResource($contact),
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
     * @param  \App\Models\Contact $contact
     */
    public function show(Contact $contact)
    {
        if (empty($contact)) {
            return response()->json([
                'status' => false,
                'message' => "Contact not found",
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => "Contact has been successfully found",
            'contact' => new AllContactResource($contact->load('user')),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     * @param  \App\Http\Requests\Contact\UpdateRequest  $request
     * @param  \App\Models\Contact $contact
     */
    public function update(UpdateRequest $request, Contact $contact)
    {
        if (empty($contact)) {
            return response()->json([
                'status' => false,
                'message' => "Contact not found",
            ], 404);
        }

        try {
            DB::beginTransaction();
            $inputs = $request->except(
                'image',
            );
            if (!empty($request->image)) {
                if (!empty($contact->image) && file_exists(public_path('storage/' . $contact->image)))
                    unlink(public_path('storage/' . $contact->image));
                $image = $request->image;
                $filename = "Image-" . time() . "-" . rand() . "." . $image->getClientOriginalExtension();
                $image->storeAs('contact', $filename, "public");
                $inputs['image'] = "contact/" . $filename;
            }
            $contact->update($inputs);
            $today_date = Carbon::now();
            $logs = new Log();
            $logs->user_id = auth()->user()->id;
            $logs->title = 'Contact Update';
            $logs->date = $today_date;
            $logs->message = 'Contact has been successfully updated at ' . $today_date;
            if (!$logs->save())
                throw new Error('Logs not saved');
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => "Contact has been successfully updated",
                'contact' => new AllContactResource($contact),
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
     * @param  \App\Models\Contact $contact
     */
    public function destroy(Contact $contact)
    {
        if (empty($contact)) {
            return response()->json([
                'status' => false,
                'message' => "Contact not found",
            ], 404);
        }

        try {
            DB::beginTransaction();
            if (!empty($contact->image) && file_exists(public_path('storage/' . $contact->image)))
                unlink(public_path('storage/' . $contact->image));
            $contact->delete();
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => "Contact has been successfully deleted",
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
