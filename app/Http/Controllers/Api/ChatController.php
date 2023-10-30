<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Chat\MessageRequest;
use App\Http\Resources\Chat\AllChatResource;
use App\Models\Chat;
use App\Models\Contact;
use App\Models\Message;
use App\Models\User;
use Error;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class ChatController extends Controller
{
    public function all_messages()
    {
        $user = auth()->user();
        $user_id = $user->id;
        $chat = Chat::has('messages')->with(['sender', 'receiver', 'messages'])->where(function ($q) use ($user_id) {
            $q->where('sender_id', $user_id);
        })->orWhere(function ($q) use ($user_id) {
            $q->where('receiver_id', $user_id);
        })->orderBy('id', 'DESC')->get();
        if (count($chat))
            return response()->json(['status' => true, 'Message' => "Chat Found", 'chat' => $chat], 200);
        else
            return response()->json(['status' => false, 'Message' => "Chat not Found", 'chat' => $chat]);
    }

    public function admin_show_chat($id)
    {
        $user_id = $id;
        $chat = Chat::with(['sender', 'receiver', 'messages'])->where(function ($q) use ($user_id) {
            $q->where('sender_id', $user_id);
        })->orWhere(function ($q) use ($user_id) {
            $q->where('receiver_id', $user_id);
        })->orderBy('id', 'DESC')->get();
        if (count($chat))
            return response()->json(['status' => true, 'Message' => "Chat Found", 'chat' => $chat], 200);
        else
            return response()->json(['status' => false, 'Message' => "Chat not Found", 'chat' => $chat]);
    }

    public function chat(Request $request)
    {
        $user = auth()->user();
        $receiver_id = $request->receiver_id;
        $user_id = $user->id;
        $chat = Chat::with(['sender', 'receiver', 'messages'])->where(function ($q) use ($user_id, $receiver_id) {
            $q->where('sender_id', $user_id)->where('receiver_id', $receiver_id);
        })->orWhere(function ($q) use ($receiver_id, $user_id) {
            $q->where('sender_id', $receiver_id)->where('receiver_id', $user_id);
        })->first();
        if (!is_object($chat)) {
            $chat = new Chat();
            $chat->sender_id = $user_id;
            $chat->receiver_id = $receiver_id;
            if ($chat->save()) {
                $chat = Chat::with(['sender', 'receiver', 'messages'])->where(function ($q) use ($user_id, $receiver_id) {
                    $q->where('sender_id', $user_id)->where('receiver_id', $receiver_id);
                })->orWhere(function ($q) use ($receiver_id, $user_id) {
                    $q->where('sender_id', $receiver_id)->where('receiver_id', $user_id);
                })->first();
            }
        }
        return response()->json(['status' => true, 'Message' => "Done", 'chat' => $chat], 200);
    }

    public function message(MessageRequest $request)
    {
        try {
            DB::beginTransaction();
            $user_id = auth()->user()->id;
            $contact = Contact::where('id', $request->contact_id)->first();
            $receiver_id = User::where('email', $contact->email)->where('is_active', 1)->first()->id;
            if (empty($receiver_id))
                throw new Error('Contact not found');
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

            if (!empty($request->voice)) {
                $voice = $request->voice;
                $filename = "Voice-" . time() . "-" . rand() . "." . $voice->getClientOriginalExtension();
                $voice->storeAs('message', $filename, "public");

                $messageData['message'] = "message/" . $filename;
                Message::create($messageData);
            }

            if (!empty($request->video)) {
                $video = $request->video;
                $filename = "Video-" . time() . "-" . rand() . "." . $video->getClientOriginalExtension();
                $video->storeAs('message', $filename, "public");

                $messageData['message'] = "message/" . $filename;
                Message::create($messageData);
            }

            if (!empty($request->message)) {
                $messageData['message'] = $request->message;
                Message::create($messageData);
            }

            $chat = Chat::with(['sender', 'receiver', 'messages.sender_message'])->where(function ($q) use ($user_id, $receiver_id) {
                $q->where('sender_id', $user_id)->where('receiver_id', $receiver_id);
            })->orWhere(function ($q) use ($receiver_id, $user_id) {
                $q->where('sender_id', $receiver_id)->where('receiver_id', $user_id);
            })->first();

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => "Message Send Successfully",
                'chat' => new AllChatResource($chat)
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
