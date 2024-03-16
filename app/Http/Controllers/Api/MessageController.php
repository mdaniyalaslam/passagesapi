<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Message\MessageRequest;
use App\Http\Resources\Message\AllChatResource;
use App\Http\Resources\Message\AllMessageResource;
use App\Models\Chat;
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
            $query = Chat::with(['user', 'receiver']);

            if (!empty($request->tab) && $request->tab == 'draft') {
                $query->where('user_id', $user_id)
                    ->whereRaw("DATE(date) = '{$date}'");
                $query->where(function ($query) use ($user_id) {
                    $query->whereHas('messages', function ($messageQuery) use ($user_id) {
                        $messageQuery->where('is_draft', 1)
                            ->where('user_id', $user_id);
                    });
                });
            }
            if (!empty($request->tab) && $request->tab == 'schedule') {
                $query->where('user_id', $user_id)
                    ->whereRaw("DATE(date) = '{$date}'");
                $query->where(function ($query) use ($user_id) {
                    $query->whereHas('messages', function ($messageQuery) use ($user_id) {
                        $messageQuery->where('is_draft', 0)
                            ->where('user_id', $user_id);
                    });
                });
            }
            // if (!empty($request->tab) && $request->tab == 'sent') {
            //     $query->where('user_id', $user_id)
            //         ->whereRaw("DATE(date) = '{$date}'");
            // }
            if (!empty($request->tab) && $request->tab == 'receive') {
                $query->where('receiver_id', $user_id)
                    ->whereRaw("DATE(date) = '{$date}'");
                $query->where(function ($query) use ($user_id) {
                    $query->whereHas('messages', function ($messageQuery) use ($user_id) {
                        $messageQuery->where('is_draft', 0)->where('is_schedule', 1)
                            ->where('receiver_id', $user_id);
                    });
                });

            }
            if (!empty($request->tab) && $request->tab == 'early_access') {
                $query->where('receiver_id', $user_id)
                    ->whereRaw("DATE(date) = '{$date}'");
                $query->where(function ($query) use ($user_id) {
                    $query->whereHas('messages', function ($messageQuery) use ($user_id) {
                        $messageQuery->where('is_draft', 0)
                            ->where('receiver_id', $user_id);
                    });
                });
            }

            $chats = $query->orderBy('id', 'DESC')->get();

            return response()->json([
                'status' => true,
                'message' => ($chats->count()) . " chat(s) found",
                'data' => AllChatResource::collection($chats),
            ]);
        } catch (Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function all_messages(Request $request)
    {
        try {
            $user_id = auth()->user()->id;
            $chat_id = $request->chat_id;
            $query = Message::with(['user', 'receiver', 'gift']);
            $query->where('chat_id', $chat_id);
            // $query->where(function ($query) use ($user_id) {
            //     $query->where('user_id', $user_id)->orWhere('receiver_id', $user_id);
            // });
            $query->where(function ($query) use ($user_id) {
                $query->where('user_id', $user_id)
                    ->orWhere(function ($query) use ($user_id) {
                        $query->where('receiver_id', $user_id)
                            ->where('is_schedule', true);
                    });
            });
            $messages = $query->orderBy('id', 'ASC')->get();
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
                throw new Error('First tell the person must register on this app before you can add an event');
            $type = $request->type ?? '';
            if (empty($type) || empty($request->message))
                throw new Error('Message Failed.');
            $chat = Chat::create([
                'user_id' => $user_id,
                'receiver_id' => $receiver->id,
                'date' => date('Y-m-d', strtotime($request->date)),
            ]);
            $messageData = [
                'chat_id' => $chat->id ?? '',
                'user_id' => $user_id ?? '',
                'receiver_id' => $receiver->id ?? '',
                'gift_id' => $request->gift_id ?? null,
                'type' => $type,
                'image_label' => $request->image_label ?? '',
                'is_draft' => (!empty($request->draft) && $request->draft == 1) ? 1 : 0,
                'schedule_date' => date('Y-m-d', strtotime($request->date)),
            ];
            $messages = []; // Initialize an array to store messages


            if ($type == 'message') {
                $messageData['message'] = $request->message ?? '';
                $message = Message::create($messageData);
                $messages[]['id'] = $message->id;
            }

            if ($type == 'voice') {
                if (is_array($request->message)) {
                    foreach ($request->message as $voices) {
                        $voice = $voices;
                        $filename = "Voice-" . time() . "-" . rand() . "." . $voice->getClientOriginalExtension();
                        $voice->storeAs('voice', $filename, "public");
                        $messageData['message'] = "voice/" . $filename;
                        $message = Message::create($messageData);
                        $messages[]['id'] = $message->id;

                    }
                } else {
                    $voice = $request->message;
                    $filename = "Voice-" . time() . "-" . rand() . "." . $voice->getClientOriginalExtension();
                    $voice->storeAs('voice', $filename, "public");
                    $messageData['message'] = "voice/" . $filename;
                    $message = Message::create($messageData);
                    $messages[]['id'] = $message->id;

                }
            }

            if ($type == 'image') {
                if (is_array($request->message)) {
                    foreach ($request->message as $images) {
                        $image = $images;
                        $filename = "Image-" . time() . "-" . rand() . "." . $image->getClientOriginalExtension();
                        $image->storeAs('image', $filename, "public");
                        $messageData['message'] = "image/" . $filename;
                        $message = Message::create($messageData);
                        $messages[]['id'] = $message->id;

                    }
                } else {
                    $image = $request->message;
                    $filename = "Image-" . time() . "-" . rand() . "." . $image->getClientOriginalExtension();
                    $image->storeAs('image', $filename, "public");
                    $messageData['message'] = "image/" . $filename;
                    $message = Message::create($messageData);
                    $messages[]['id'] = $message->id;

                }
            }

            if ($type == 'video') {
                if (is_array($request->message)) {
                    foreach ($request->message as $videos) {
                        $video = $videos;
                        $filename = "Video-" . time() . "-" . rand() . "." . $video->getClientOriginalExtension();
                        $video->storeAs('video', $filename, "public");
                        $messageData['message'] = "video/" . $filename;
                        $message = Message::create($messageData);
                        $messages[]['id'] = $message->id;

                    }
                } else {
                    $video = $request->message;
                    $filename = "Video-" . time() . "-" . rand() . "." . $video->getClientOriginalExtension();
                    $video->storeAs('video', $filename, "public");
                    $messageData['message'] = "video/" . $filename;
                    $message = Message::create($messageData);
                    $messages[]['id'] = $message->id;

                }
            }
            $messageIds = array_column($messages, 'id');
            $mess = Message::whereIn('id', $messageIds)->get();
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => "Message Send Successfully",
                'messages' => AllMessageResource::collection($mess)
            ]);
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function message_sent(MessageRequest $request)
    {
        try {
            DB::beginTransaction();
            $user_id = auth()->user()->id;
            $receiver = User::where('id', $request->receiver_id)->where('is_active', 1)->first();
            if (empty($receiver))
                throw new Error('Receiver not exist');

            $messageData = [
                'chat_id' => $request->chat_id ?? '',
                'user_id' => $user_id ?? '',
                'receiver_id' => $receiver->id ?? '',
                'gift_id' => $request->gift_id ?? null,
                'is_draft' => (!empty($request->draft) && $request->draft == 1) ? 1 : 0,
                'is_schedule' => (!empty($request->draft) && $request->draft == 1) ? 0 : 1,
                'schedule_date' => date('Y-m-d', strtotime($request->date)),
                'type' => $request->type,
                'image_label' => $request->image_label ?? '',
            ];
            $type = $request->type ?? '';
            if (empty($type) || empty($request->message))
                throw new Error('Message Failed.');

            $messages = []; // Initialize an array to store messages
            if ($type == 'message') {
                $messageData['message'] = $request->message ?? '';
                $message = Message::create($messageData);
                $messages[] = array_merge($messageData, ['id' => $message->id]);
            }

            if ($type == 'voice') {
                if (is_array($request->message)) {
                    foreach ($request->message as $voices) {
                        $voice = $voices;
                        $filename = "Voice-" . time() . "-" . rand() . "." . $voice->getClientOriginalExtension();
                        $voice->storeAs('voice', $filename, "public");
                        $messageData['message'] = "voice/" . $filename;
                        $message = Message::create($messageData);
                        $messages[] = array_merge($messageData, ['id' => $message->id, 'url' => request()->getSchemeAndHttpHost() . '/storage/']);
                    }
                } else {
                    $voice = $request->message;
                    $filename = "Voice-" . time() . "-" . rand() . "." . $voice->getClientOriginalExtension();
                    $voice->storeAs('voice', $filename, "public");
                    $messageData['message'] = "voice/" . $filename;
                    $message = Message::create($messageData);
                    $messages[] = array_merge($messageData, ['id' => $message->id, 'url' => request()->getSchemeAndHttpHost() . '/storage/']);
                }
            }

            if ($type == 'image') {
                if (is_array($request->message)) {
                    foreach ($request->message as $images) {
                        $image = $images;
                        $filename = "Image-" . time() . "-" . rand() . "." . $image->getClientOriginalExtension();
                        $image->storeAs('image', $filename, "public");
                        $messageData['message'] = "image/" . $filename;
                        $message = Message::create($messageData);
                        $messages[] = array_merge($messageData, ['id' => $message->id, 'url' => request()->getSchemeAndHttpHost() . '/storage/']);
                    }
                } else {
                    $image = $request->message;
                    $filename = "Image-" . time() . "-" . rand() . "." . $image->getClientOriginalExtension();
                    $image->storeAs('image', $filename, "public");
                    $messageData['message'] = "image/" . $filename;
                    $message = Message::create($messageData);
                    $messages[] = array_merge($messageData, ['id' => $message->id, 'url' => request()->getSchemeAndHttpHost() . '/storage/']);
                }
            }

            if ($type == 'video') {
                if (is_array($request->message)) {
                    foreach ($request->message as $videos) {
                        $video = $videos;
                        $filename = "Video-" . time() . "-" . rand() . "." . $video->getClientOriginalExtension();
                        $video->storeAs('video', $filename, "public");
                        $messageData['message'] = "video/" . $filename;
                        $message = Message::create($messageData);
                        $messages[] = array_merge($messageData, ['id' => $message->id, 'url' => request()->getSchemeAndHttpHost() . '/storage/']);
                    }
                } else {
                    $video = $request->message;
                    $filename = "Video-" . time() . "-" . rand() . "." . $video->getClientOriginalExtension();
                    $video->storeAs('video', $filename, "public");
                    $messageData['message'] = "video/" . $filename;
                    $message = Message::create($messageData);
                    $messages[] = array_merge($messageData, ['id' => $message->id, 'url' => request()->getSchemeAndHttpHost() . '/storage/']);
                }
            }
            $pusher = new \Pusher\Pusher(env('PUSHER_APP_KEY'), env('PUSHER_APP_SECRET'), env('PUSHER_APP_ID'), array('cluster' => env('PUSHER_APP_CLUSTER')));
            if (!$pusher->trigger('user-' . $user_id, 'message', $messages))
                throw new Error("Message not send!");
            $messageIds = array_column($messages, 'id');
            $mess = Message::whereIn('id', $messageIds)->get();
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => "Message Send Successfully",
                'messages' => AllMessageResource::collection($mess)
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
            'messages' => new AllMessageResource($message->load(['user', 'receiver', 'gift'])),
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
                if (!empty($message->voice) && file_exists(public_path('storage/' . $message->voice)))
                    unlink(public_path('storage/' . $message->voice));
                $voice = $request->voice;
                $filename = "Voice-" . time() . "-" . rand() . "." . $voice->getClientOriginalExtension();
                $voice->storeAs('message', $filename, "public");

                $messageData['voice'] = "message/" . $filename;
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
            $user_id = auth()->user()->id;
            $messages = Message::where('chat_id',$id)->where('receiver_id', $user_id)->where('is_schedule', 1)->get();
            if (!empty($messages) && count($messages) > 0) {
                foreach ($messages as $message) {
                    $message->is_read = 1;
                    $message->save();
                }
            }
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
