<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\NotiSend;
use App\Models\AppNotification;
use App\Models\Message;
use Carbon\Carbon;
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
        $date = Carbon::now()->format('Y-m-d');
        $query = Message::where('is_schedule' , 0)->where('is_read' , 0)->where('is_draft' , 0);
        $messages = $query->whereRaw("DATE(schedule_date) = '{$date}'")->get();
        if(!empty($messages) && count($messages) > 0){
            foreach ($messages as  $message) {
                $message->is_schedule = true;
                if (!$message->save()) throw new Error("Message not schedule");
            }
            // $title = 'New Message';
            // $message = 'You have recieved new message';
            // $appnot = new AppNotification();
            // $appnot->user_id = $user->id;
            // $appnot->notification = $message;
            // $appnot->navigation = $title;
            // $appnot->save();
            // NotiSend::sendNotif($user->device_token, '', $title, $message);
        }
    }
}
