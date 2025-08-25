<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class BkashController extends Controller
{
    private $baseUrl;
    private $username;
    private $password;
    private $appKey;
    private $appSecret;

    public function __construct()
    {
        $this->baseUrl   = "https://sbdynamic.pay.bka.sh/v1";
        $this->username  = "data";
        $this->password  = "data";
        $this->appKey    = "data";
        $this->appSecret = "data";
    }

    private function getToken()
    {
        return Cache::remember('bkash_token', 55 * 60, function () {
            $url = $this->baseUrl . "/auth/grant-token";

            $headers = [
                "username: {$this->username}",
                "password: {$this->password}",
                "Content-Type: application/json"
            ];

            $data = json_encode([
                "app_key"    => $this->appKey,
                "app_secret" => $this->appSecret
            ]);

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

            $response = curl_exec($ch);
            curl_close($ch);

            $res = json_decode($response, true);

            if (!isset($res['id_token']) && !isset($res['access_token'])) {
                abort(500, "bKash Token Error: " . $response);
            }

            return $res['id_token'] ?? $res['access_token'];
        });
    }

    /**
     * Create Payment
     */
    public function checkout(Request $request)
    {
        $url = $this->baseUrl . "/payment/create";

        $headers = [
            "X-APP-Key: {$this->appKey}",
            "Authorization: " . $this->getToken(),
            "Content-Type: application/json"
        ];

        $data = json_encode([
            "amount" => "100",
            "currency" => "BDT",
            "intent" => "sale",
            "mode" => "1011",
            "payerReference" => "017XXXXXXXX",
            "callbackURL" => url('/bkash/callback'),
            "successCallbackURL" => url('/bkash/success'),
            "failureCallbackURL" => url('/bkash/fail'),
            "cancelledCallbackURL" => url('/bkash/cancel'),
            "merchantInvoiceNumber" => uniqid("inv_")
        ]);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $response = curl_exec($ch);
        curl_close($ch);

        $res = json_decode($response, true);

        #Sample response:
//        {
//            "paymentId": "DCPAY1011T2aSWHN1756113219270",
//            "paymentCreateTime": "2025-08-25T15:13:39:270 GMT+0600",
//            "transactionStatus": "Initiated",
//            "amount": "100",
//            "currency": "BDT",
//            "intent": "sale",
//            "merchantInvoiceNumber": "inv_68ac2942ed178",
//            "bkashURL": "https://ui.sbdynamic.pay.bka.sh/?paymentId=DCPAY1011T2aSWHN1756113219270&hash=t.!RJ7RlvzHJ1PutVD6zMdv*i4ZnVUwU73tJZ0PkwhV.RQCsjM4wGUsjUgZqmp3IXJH9M7gyHRgmrf0d0PM7zohOUNvDdzcj4AkN1756113219270&mode=1011",
//            "callbackURL": "http://127.0.0.1:8000/bkash/callback",
//            "successCallbackURL": "http://127.0.0.1:8000/bkash/callback?paymentId=DCPAY1011T2aSWHN1756113219270&status=success",
//            "failureCallbackURL": "http://127.0.0.1:8000/bkash/callback?paymentId=DCPAY1011T2aSWHN1756113219270&status=failure",
//            "cancelledCallbackURL": "http://127.0.0.1:8000/bkash/callback?paymentId=DCPAY1011T2aSWHN1756113219270&status=cancel",
//            "payerReference": "017XXXXXXXX"
//        }

        if (isset($res['bkashURL'])) {
            return redirect($res['bkashURL']);
        }

        return response()->json($res);
    }

    /**
     *  Handle Callback (POST from bKash)
     */
    public function callback(Request $request)
    {
        $paymentId = $request->input('paymentID') ?? $request->input('paymentId');

        if (!$paymentId) {
            return redirect('/bkash/fail');
        }

        // Execute payment
        $url = $this->baseUrl . "/payment/execute";

        $headers = [
            "X-APP-Key: {$this->appKey}",
            "Authorization: " . $this->getToken(),
            "Content-Type: application/json"
        ];

        $data = json_encode([
            "paymentId" => $paymentId
        ]);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $response = curl_exec($ch);
        curl_close($ch);

        $res = json_decode($response, true);

//        Payment::updateOrCreate(
//            ['payment_id' => $res['paymentId']],
//            [
//                'transaction_id' => $res['trxID'] ?? null,
//                'amount' => $res['amount'] ?? null,
//                'service_fee' => $res['serviceFee'] ?? 0,
//                'status' => strtolower($res['transactionStatus'] ?? 'failed'), // completed, failed, cancelled
//                'payer_reference' => $res['payerReference'] ?? null,
//                'merchant_invoice' => $res['merchantInvoiceNumber'] ?? null,
//                'raw_response' => json_encode($res)
//            ]
//        );

        $status = strtolower($res['transactionStatus'] ?? 'failed');

        if ($status === 'completed') {
            return redirect('/bkash/success');
        } elseif ($status === 'cancelled') {
            return redirect('/bkash/cancel');
        } else {
            return redirect('/bkash/fail');
        }
    }

    public function success(Request $request)
    {
        return 'Payment Successful';
    }

    public function failure(Request $request)
    {

        return 'Payment Failed';
    }

    public function cancel(Request $request)
    {
        return 'Payment Cancelled';
    }

    public function query($paymentId)
    {
        $url = $this->baseUrl . "/query/payment";

        $headers = [
            "X-APP-Key: {$this->appKey}",
            "Authorization: " . $this->getToken(),
            "Content-Type: application/json"
        ];

        $data = json_encode([
            "paymentId" => $paymentId
        ]);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $response = curl_exec($ch);
        curl_close($ch);

        return response()->json(json_decode($response, true));
    }
}
