<?php

namespace App\Http\Controllers;

use Yabacon\Paystack;

use App\Http\Requests;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;


class PaymentController extends Controller
{

    public function initPayment()
    {
        try {
            $reference = $this->generateReference();
            $amount = request('amount') * 100;
            $data = array(
                "amount" => $amount,
                "reference" => "$reference",
                "email" => Auth::user()->email,
            );

            $paystack = new Paystack(config('services.paystack.secret_key'));
            $transaction = $paystack->transaction->initialize($data);
        } catch (\Yabacon\Paystack\Exception\ApiException $e) {
            print_r($e->getResponseObject());
            die($e->getMessage());
        }

        $registerTransaction = Transaction::create([
            'user_id' => Auth::id(),
            'buyer_id' => Auth::id(),
            'amount' => $amount,
            'transaction_type' => 'funding',
            'status' => 'pending',
            'ref_no' => $reference,
        ]);

        if ($registerTransaction) {
            return redirect($transaction->data->authorization_url);
        } else {
            return redirect()->back()->with('error', 'Payment initialization failed. Please try again.');
        }
    }

    /**
     * Obtain Paystack payment information
     * @return void
     */
    public function confirmPayment(Request $request)
    {
        // Verify the event
       


        // if ($payload['event'] !== 'charge.success') {
        //     return response()->json(['status' => 'error']);
        // }

        // Retrieve the transaction data
        // $data = $payload['data'];
        $reference = $request->reference;
        // $status = $request['status'];
        $amount = $request->trx_ref;

        // Verify the transaction with Paystack API (recommended)
        $paystack = new Paystack(config('services.paystack.secret_key'));
        $transaction = $paystack->transaction->verify([$reference]);


        return $transaction;
        // if ($transaction->data->status === 'success') {
            // Update your database records
            // $payment = Payment::where('reference', $reference)->first();
            // if ($payment) {
            //     $payment->update([
            //         'status' => 'completed',
            //         'paid_at' => now(),
            //         'gateway_response' => $data['gateway_response']
            //     ]);
            // }

            // You might want to trigger other actions like sending email confirmations
        // }

      //  return response()->json(['status' => 'success']);
    }
    public function generateReference()
    {
        $prefix = 'TRX';
        do {
            $timestamp = now()->format('YmdHis');
            $randomString = strtoupper(Str::random(6));
            $reference_no = $prefix . $timestamp . $randomString . Auth::id();

            $exists = Transaction::where('ref_no', '=', $reference_no)->first();
        } while ($exists);

        return $reference_no;
    }
}
