<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Gift\PaymentRequest;
use Error;
use Stripe;
use Throwable;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
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
                "description" => "Test payment from IcotSolutions."
            ]);
            $payment->stripe_id = $charge->id;
            $payment->total = $total;
            $payment->status = true;
            if (!$payment->save())
                throw new Error("Payment UnSuccessfull!");
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Payment Successfull'
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
