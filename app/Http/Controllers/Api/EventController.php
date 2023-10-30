<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Event\StoreRequest;
use App\Http\Requests\Event\UpdateRequest;
use App\Http\Resources\Event\AllEventResource;
use App\Models\Chat;
use App\Models\Contact;
use App\Models\Event;
use App\Models\Log;
use App\Models\Message;
use App\Models\User;
use Carbon\Carbon;
use Error;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = Event::with(['user', 'contact']);
            if (!empty($request->date))
                $query->whereRaw("DATE(date) = '{$request->date}'");
            if (!empty($request->skip))
                $query->skip($request->skip);
            if (!empty($request->take))
                $query->take($request->take);
            $event = $query->orderBy('id', 'DESC')->get();
            return response()->json([
                'status' => true,
                'message' => ($event->count()) . " event(s) found",
                'data' => AllEventResource::collection($event),
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
     * @param  \App\Http\Requests\Event\StoreRequest  $request
     */
    public function store(StoreRequest $request)
    {
        try {
            DB::beginTransaction();
            $user_id = auth()->user()->id;
            $inputs = $request->except(
                'user_id',
            );
            $inputs['user_id'] = $user_id;
            $event = Event::create($inputs);

            $contact = Contact::where('id', $request->contact_id)->first();
            $receiver = User::where('email', $contact->email)->where('is_active', 1)->first();
            if (empty($receiver))
                throw new Error('First tell the person to register on this app and then you can add them');
            $receiver_id = $receiver->id;
            $chat = Chat::where(function ($q) use ($user_id, $receiver_id) {
                $q->where('sender_id', $user_id)->where('receiver_id', $receiver_id);
            })->orWhere(function ($q) use ($receiver_id, $user_id) {
                $q->where('sender_id', $receiver_id)->where('receiver_id', $user_id);
            })->first();

            if (!is_object($chat)) {
                $chat = new Chat();
                $chat->sender_id = $user_id;
                $chat->receiver_id = $receiver_id;
                if (!$chat->save())
                    throw new Error("Chat not save!");
            }

            $messageData = [
                'chat_id' => $chat->id,
                'sender_id' => $user_id,
                'schedule_date' => date('Y-m-d', strtotime($request->date)),
            ];

            if (!empty($request->name)) {
                $messageData['message'] = $request->name;
                Message::create($messageData);
            }

            if (!empty($request->desc)) {
                $messageData['message'] = $request->desc;
                Message::create($messageData);
            }

            $today_date = Carbon::now();
            $logs = new Log();
            $logs->user_id = auth()->user()->id;
            $logs->title = 'Event Add';
            $logs->date = $today_date;
            $logs->message = 'New Event has been successfully added at ' . $today_date;
            if (!$logs->save())
                throw new Error('Logs not saved');
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => "Event has been successfully added.",
                'event' => new AllEventResource($event),
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
     * @param  \App\Models\Event $event
     */
    public function show(Event $event)
    {
        if (empty($event)) {
            return response()->json([
                'status' => false,
                'message' => "Event not found",
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => "Event has been successfully found",
            'event' => new AllEventResource($event->load(['user', 'contact'])),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     * @param  \App\Http\Requests\Event\UpdateRequest  $request
     * @param  \App\Models\Event $event
     */
    public function update(UpdateRequest $request, Event $event)
    {
        if (empty($event)) {
            return response()->json([
                'status' => false,
                'message' => "Event not found",
            ], 404);
        }

        try {
            DB::beginTransaction();
            $event->update($request->validated());

            $today_date = Carbon::now();
            $logs = new Log();
            $logs->user_id = auth()->user()->id;
            $logs->title = 'Event Update';
            $logs->date = $today_date;
            $logs->message = 'Event has been successfully updated at ' . $today_date;
            if (!$logs->save())
                throw new Error('Logs not saved');
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => "Event has been successfully updated",
                'event' => new AllEventResource($event),
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
     * @param  \App\Models\Event $event
     */
    public function destroy(Event $event)
    {
        if (empty($event)) {
            return response()->json([
                'status' => false,
                'message' => "Event not found",
            ], 404);
        }

        try {
            DB::beginTransaction();
            $event->delete();
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => "Event has been successfully deleted",
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
