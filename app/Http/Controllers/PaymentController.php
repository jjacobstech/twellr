<?php

namespace App\Http\Controllers;

use Yabacon\Paystack;

use App\Http\Requests;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;


class PaymentController extends Controller
{

    public function initPayment()
    {
        try {
            $data = array(
                "amount" => 700 * 100,
                "reference" => '4g4g5485g8545jg8gj',
                "email" => 'user@mail.com',
                "currency" => "NGN",
                "orderID" => 23456,
            );
            Paystack
        } catch (\Exception $e) {
            return Redirect::back()->withMessage(['msg' => 'The paystack token has expired. Please refresh the page and try again.', 'type' => 'error']);
        }
    }

    /**
     * Obtain Paystack payment information
     * @return void
     */
    public function confirmPayment()
    {
        $paymentDetails = Paystack::getPaymentData();

        dd($paymentDetails);
        // Now you have the payment details,
        // you can store the authorization_code in your db to allow for recurrent subscriptions
        // you can then redirect or do whatever you want
    }
}
