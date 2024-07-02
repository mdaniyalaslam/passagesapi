<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Gift\PaymentRequest;
use App\Http\Requests\Gift\SendRequest;
use App\Http\Resources\Gift\AllPaymentResource;
use App\Models\Contact;
use App\Models\GiftPayment;
use App\Models\Payment;
use App\Models\User;
use Error;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Stripe;
use Throwable;

class PaymentController extends Controller
{
    public function gift_payment_index(Request $request)
    {
        try {
            $query = GiftPayment::with(['user', 'receiver']);
            $user = auth()->user()->load('role');
            if ($user->role->name === "user") {
                if ($request->type == 'send') {
                    $query->where('user_id', $user->id)->where('status', true);
                } else if ($request->type == 'receive') {
                    $query->where('receiver_id', $user->id)->where('status', true);
                } else {
                    $query->where('user_id', $user->id)->orWhere('receiver_id', $user->id)->where('status', true);
                }
            }
            if ($request->skip) {
                $query->skip($request->skip);
            }

            if ($request->take) {
                $query->take($request->take);
            }

            $gift_payment = $query->orderBy('id', 'DESC')->get();
            return response()->json([
                'status' => true,
                'message' => ($gift_payment->count()) . " gift_payment(s) found",
                'data' => AllPaymentResource::collection($gift_payment),
            ]);
        } catch (Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function gift_payment(PaymentRequest $request)
    {
        try {
            DB::beginTransaction();
            $total = $request->total;
            $token = $request->token;

            $payment = new Payment();
            $payment->user_id = auth()->user()->id;
            $payment->gift_id = $request->gift_id;
            $payment->payment_method = "stripe";
            Stripe\Stripe::setApiKey(env('SECRET_KEY'));
            $charge = Stripe\Charge::create([
                "amount" => round($total, 2) * 100,
                "currency" => "usd",
                "source" => $token,
                "description" => "Test payment from IcotSolutions.",
            ]);
            $payment->stripe_id = $charge->id;
            $payment->total = $total;
            $payment->status = true;
            if (!$payment->save()) {
                throw new Error("Payment UnSuccessfull!");
            }

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Payment Successfull',
            ]);
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function send_gift(SendRequest $request)
    {
        try {
            DB::beginTransaction();
            $inputs = $request->all();
            $user = auth()->user();
            $amount = $request->amount;
            $token = $request->token;
            $contact = Contact::where('id', $request->receiver_id)->first();
            if (empty($contact)) {
                throw new Error('Contact not found');
            }

            $receiver = User::where('email', $contact->email)->where('is_active', 1)->first();
            if (empty($receiver)) {
                throw new Error('First tell the person must register on this app before you can send gift');
            }

            Stripe\Stripe::setApiKey(env('SECRET_KEY'));
            $account = Stripe\Account::retrieve($receiver->accountId, []);
            if (!$account || !$account->details_submitted || !$account->charges_enabled || !$account->payouts_enabled) {
                throw new Error('Account detailed not submitted');
            }

            $charge_data = [
                "amount" => round($amount, 2) * 100,
                "currency" => "usd",
                "source" => $token,
                "description" => "Test payment.",
                "transfer_data" => ['destination' => $account->id],
            ];
            $charge = Stripe\Charge::create($charge_data);

            $inputs['user_id'] = $user->id;
            $inputs['receiver_id'] = $receiver->id;
            $inputs['status'] = true;
            $inputs['stripe_id'] = $charge->id;
            if (!GiftPayment::create($inputs)) {
                throw new Error("Payment failed!");
            }

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Payment Successfull',
            ]);
        } catch (Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

}
