<?php

namespace App\Http\Controllers;

use App\Models\User;

use Yabacon\Paystack;
use App\Http\Requests;
use App\Models\Deposit;
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
            $funding_amount = request('amount');
            $amount = request('amount') * 100;
            $data = array(
                "amount" => $amount,
                "reference" => "$reference",
                "email" => Auth::user()->email,
            );

            $paystack = new Paystack(config('services.paystack.secret_key'));
            $transactionInit = $paystack->transaction->initialize($data);
        } catch (\Yabacon\Paystack\Exception\ApiException $e) {
            print_r($e->getResponseObject());
            die($e->getMessage());
        }

        $transaction = Transaction::create([
            'user_id' => Auth::id(),
            'buyer_id' => Auth::id(),
            'amount' => $funding_amount,
            'transaction_type' => 'funding',
            'status' => 'pending',
            'ref_no' => $reference,
        ]);

        if ($transaction) {

            $deposit = Deposit::create([
                'user_id' => Auth::id(),
                'transaction_id' => $transaction->id,
                'ref_no' => $reference,
                'amount' => $funding_amount,
                'status' => 'pending',
            ]);

            if ($deposit) {
                return redirect($transactionInit->data->authorization_url);
            } else {
                return redirect()->back()->with('error', 'Failed to create deposit. Please try again.');
            }
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

        try {

            $trxref = $request->trxref;
            $data = ['reference' => $trxref];

            $paystack = new Paystack(config('services.paystack.secret_key'));
            $transaction = $paystack->transaction->verify($data);
        } catch (\Yabacon\Paystack\Exception\ApiException $e) {
            print_r($e->getResponseObject());
            die($e->getMessage());
        }

        if ($transaction->data->status === 'success') {

            $amount = $transaction->data->amount/100;
            $reference = $transaction->data->reference;
            $payment = Transaction::where('ref_no', $reference)->first();

            if ($payment) {
                $payment->update([
                    'user_id' => Auth::id(),
                    'buyer_id' => Auth::id(),
                    'amount' => $amount,
                    'transaction_type' => 'funding',
                    'status' => 'completed',
                    'ref_no' => $reference,
                ]);

                $deposit = Deposit::where('ref_no', $reference)->first();


                if ($deposit) {
                    $deposit->update([
                        'user_id' => Auth::id(),
                        'transaction_id' => $payment->id,
                        'ref_no' => $reference,
                        'amount' => $amount,
                        'status' => 'completed',
                    ]);

                    // Update the user's account balance or perform any other necessary actions
                    $fund = User::find(Auth::id());
                    $fund->wallet_balance = $fund->wallet_balance + $amount ; // Assuming the amount is in kobo
                    $funded =  $fund->save();

                    if (!$funded) {
                        return redirect(route('wallet'))->with('error', 'Failed to fund your account. Please try again.');
                    } else {
                        return redirect(route('wallet'))->with('success', 'Payment successful. Your account has been funded.');
                    }
                } else {

                    $payment->update([
                        'user_id' => Auth::id(),
                        'buyer_id' => Auth::id(),
                        'amount' => $amount,
                        'transaction_type' => 'funding',
                        'status' => 'failed',
                        'ref_no' => $reference,
                    ]);

                    return redirect(route('wallet'))->with('error', 'Deposit not found.');
                }
            } else {
                return redirect(route('wallet'))->with('error', 'Transaction not found.');
            }
        } else {
            $payment = Transaction::where('ref_no', $trxref)->first();

            if ($payment) {
                $payment->update([
                    'user_id' => Auth::id(),
                    'buyer_id' => Auth::id(),
                    'transaction_type' => 'funding',
                    'status' => 'failed',
                    'ref_no' => $trxref,
                ]);

                $deposit = Deposit::update([
                    'user_id' => Auth::id(),
                    'transaction_id' => $transaction->id,
                    'ref_no' => $trxref,
                    'status' => 'pending',
                ]);

                if ($deposit) {
                    return redirect(route('wallet'))->with('error', 'Payment failed. Please try again.');
                } else {
                    return redirect(route('wallet'))->with('error', 'Failed to update deposit status. Please try again.');
                }
            }

            return redirect(route('wallet'))->with('error', 'Payment failed. Please try again.');
        }
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
