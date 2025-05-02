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
        return request()->amount;
        try {
            $reference = $this->generateReference();
            $data = array(
                //"amount" => 700 * 100,
                "reference" => "$reference",
                "email" => Auth::user()->email,
            );

            $paystack = new Paystack(env('PAYSTACK_SECRET_KEY'));
            $transaction = $paystack->transaction->initialize($data);

        } catch (\Yabacon\Paystack\Exception\ApiException $e) {
           print_r($e->getResponseObject());
           die($e->getMessage());
        }
      //  $paystack->save_last_transaction_reference($transaction->data->reference);

      return redirect($transaction->data->authorization_url);
    }

    /**
     * Obtain Paystack payment information
     * @return void
     */
    public function confirmPayment()
    {
        // $paymentDetails = Paystack::getPaymentData();

        // dd($paymentDetails);
        // Now you have the payment details,
        // you can store the authorization_code in your db to allow for recurrent subscriptions
        // you can then redirect or do whatever you want
    }
   public function generateReference () {
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
