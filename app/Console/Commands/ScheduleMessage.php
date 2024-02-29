<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\NotiSend;
use App\Models\AppNotification;
use App\Models\Message;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Error;
use Illuminate\Console\Command;

class ScheduleMessage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schedule:message';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'All Schedule Message Sent to the User';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            DB::beginTransaction();
            $date = Carbon::now()->format('Y-m-d');
            $query = Message::where('is_schedule' , 0)->where('is_read' , 0)->where('is_draft' , 0);
            $messages = $query->whereRaw("DATE(schedule_date) = '{$date}'")->get();
            
            if(!empty($messages) && count($messages) > 0){
                foreach ($messages as  $originalMessage) {
                    
                    $originalMessage->is_schedule = true;
                    if (!$originalMessage->save()) throw new Error("Message not schedule");
                    $title = 'Scheduled Message';
                    $notificationMessage = ($originalMessage->type == 'message') ? $originalMessage->message : 'Sent you a message';
                    $notificationImage = ($originalMessage->type == 'image') ? $originalMessage->message : '';
                    $receiver = User::where('id' , $originalMessage->receiver_id)->first();
                    
                    $appnot = new AppNotification();
                    $appnot->sender_id = $originalMessage->user_id;
                    $appnot->receiver_id = $receiver->id;
                    $appnot->notification = $notificationMessage;
                    $appnot->right_image = $notificationImage;
                    $appnot->navigation = $title;
                    $appnot->date = $date;
                    $appnot->save();
    
                    // NotiSend::sendNotif($receiver->device_token, '', $title, $notificationMessage);
                }
            } 
            DB::commit();
        } catch (\Throwable $th){
            DB::rollback();
        }
    }
}
