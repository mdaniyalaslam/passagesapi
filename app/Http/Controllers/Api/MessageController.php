<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Message\MessageRequest;
use App\Http\Resources\Message\AllMessageResource;
use App\Models\Contact;
use App\Models\Message;
use App\Models\User;
use Carbon\Carbon;
use Error;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class MessageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $user_id = auth()->user()->id;
            $date = $request->date ?? Carbon::now()->format('Y-m-d');
            $query = Message::with(['user', 'contact', 'gift']);
            if (!empty($request->tab) && $request->tab == 'draft')
                $query->where('user_id', $user_id)->where('is_schedule', 0)->where('is_draft', 1);
            if (!empty($request->tab) && $request->tab == 'schedule')
                $query->where('user_id', $user_id)->where('is_schedule', 0)->where('is_draft', 0);
            if (!empty($request->tab) && $request->tab == 'sent')
                $query->where('user_id', $user_id)->where('is_schedule', 1)->where('is_draft', 0);
            if (!empty($request->tab) && $request->tab == 'receive')
                $query->where('contact_id', $user_id)->where('is_schedule', 1)->where('is_draft', 0);
            if (!empty($request->date))
                $query->whereRaw("DATE(schedule_date) = '{$date}'");
            $messages = $query->orderBy('id', 'DESC')->get();
            return response()->json([
                'status' => true,
                'message' => ($messages->count()) . " message(s) found",
                'data' => AllMessageResource::collection($messages),
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
     * @param  \App\Http\Requests\Message\MessageRequest  $request
     */
    public function store(MessageRequest $request)
    {
        try {
            DB::beginTransaction();
            $user_id = auth()->user()->id;
            $contact = Contact::where('id', $request->contact_id)->first();
            if (empty($contact))
                throw new Error('Contact not found');
            $receiver = User::where('email', $contact->email)->where('is_active', 1)->first();
            if (empty($receiver))
                throw new Error('First tell the person to register on this app and then you can add event');

            $messageData = [
                'user_id' => $user_id ?? '',
                'contact_id' => $request->contact_id ?? '',
                'gift_id' => $request->gift_id ?? null,
                'message' => $request->message ?? '',
                'video' => $request->video ?? '',
                'is_draft' => (!empty($request->draft) && $request->draft == 1) ? 1 : 0,
                'schedule_date' => date('Y-m-d', strtotime($request->date)),
            ];

            if (!empty($request->voice)) {
                $voice = $request->voice;
                $filename = "Voice-" . time() . "-" . rand() . "." . $voice->getClientOriginalExtension();
                $voice->storeAs('message', $filename, "public");

                $messageData['voice'] = request()->getSchemeAndHttpHost() . "/storage/message/" . $filename;
            }

            $message = Message::create($messageData);

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => "Message Send Successfully",
                'messages' => new AllMessageResource($message)
            ]);
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

   /**
     * Display the specified resource.
     * @param  \App\Models\Message $message
     */
    public function show(Message $message)
    {
        if (empty($message)) {
            return response()->json([
                'status' => false,
                'message' => "Message not found",
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => "Message has been successfully found",
            'messages' => new AllMessageResource($message->load(['user', 'contact', 'gift'])),
        ]);
    }

    /**
     * Update the specified resource in storage.
     * @param  \App\Http\Requests\Message\MessageRequest  $request
     */
    public function update(MessageRequest $request, Message $message)
    {
        try {
            DB::beginTransaction();
            $user_id = auth()->user()->id;
            $contact = Contact::where('id', $request->contact_id)->first();
            if (empty($contact))
                throw new Error('Contact not found');
            $receiver = User::where('email', $contact->email)->where('is_active', 1)->first();
            if (empty($receiver))
                throw new Error('First tell the person to register on this app and then you can add event');

            $messageData = [
                'user_id' => $user_id ?? '',
                'contact_id' => $request->contact_id ?? '',
                'gift_id' => $request->gift_id ?? null,
                'message' => $request->message ?? '',
                'video' => $request->video ?? '',
                'is_draft' => 0,
                'is_schedule' => 0,
                'is_read' => 0,
                'schedule_date' => date('Y-m-d', strtotime($request->date)),
            ];

            if (!empty($request->voice)) {
                if (!empty($message->voice) && file_exists(public_path('storage/' . $message->voice))) unlink(public_path('storage/' . $message->voice));
                $voice = $request->voice;
                $filename = "Voice-" . time() . "-" . rand() . "." . $voice->getClientOriginalExtension();
                $voice->storeAs('message', $filename, "public");

                $messageData['voice'] = request()->getSchemeAndHttpHost() . "/storage/message/" . $filename;
            }

            $message->update($messageData);

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => "Message Send Successfully",
                'messages' => new AllMessageResource($message)
            ]);
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Message $message)
    {
        //
    }

    public function read($id)
    {
        try {
            DB::beginTransaction();
            $message = Message::where('id' , $id)->first();
            $message->is_read = 1;
            $message->save();

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => "Message Read Successfully",
            ]);
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
