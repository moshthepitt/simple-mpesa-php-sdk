<?php

class Mpesa
{
    public $access_token;
    public $access_token_expires_in;

    function __construct($consumer_key, $consumer_secret, $url = 'https://sandbox.safaricom.co.ke')
    {
        $this->url = $url;
        $this->consumer_key = $consumer_key;
        $this->consumer_secret = $consumer_secret;
    }

    /**
     * use this function to generate and set the API token
     */
    public function generateToken()
    {

        $curl = curl_init();
        $url = $this->url . '/oauth/v1/generate?grant_type=client_credentials';
        curl_setopt($curl, CURLOPT_URL, $url);
        $credentials = base64_encode($this->consumer_key . ':' . $this->consumer_secret);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Authorization: Basic ' . $credentials)); //setting a custom header
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $curl_response = curl_exec($curl);
        $response = json_decode($curl_response);

        $this->access_token = $response->access_token;
        $this->access_token_expires_in = $response->expires_in;
    }

    /**
     * Use this function to initiate an STKPush Simulation
     * @param $BusinessShortCode | The organization shortcode used to receive the transaction.
     * @param $LipaNaMpesaPasskey | The Lipa Na MPESA passkey.  Get it from https://developer.safaricom.co.ke/test_credentials
     * @param $timestamp | This is the Timestamp of the transaction in this format YYYYMMDDHHmmss
     * @return string
     */
    public function getPassword($BusinessShortCode, $LipaNaMpesaPasskey, $timestamp)
    {
        return base64_encode($BusinessShortCode . $LipaNaMpesaPasskey . $timestamp);
    }

    /**
     * Use this function to initiate an STKPush Simulation
     * @param $BusinessShortCode | The organization shortcode used to receive the transaction.
     * @param $LipaNaMpesaPasskey | The Lipa Na MPESA passkey.  Get it from https://developer.safaricom.co.ke/test_credentials
     * @param $TransactionType | The transaction type to be used for this request. Only CustomerPayBillOnline is supported.
     * @param $Amount | The amount to be transacted.
     * @param $PartyA | The phone number sending money.
     * @param $PartyB | The organization shortcode receiving the funds
     * @param $PhoneNumber | The Mobile Number to receive the STK Pin Prompt. Can be same as $PartyA.
     * @param $CallBackURL | The url to where responses from M-Pesa will be sent to.
     * @param $AccountReference | Account Reference: This is an Alpha-Numeric parameter that is defined by your system as an Identifier of the transaction for CustomerPayBillOnline transaction type. Along with the business name, this value is also displayed to the customer in the STK Pin Prompt message. Maximum of 12 characters.
     * @param $TransactionDesc | A description of the transaction.  This is any additional information/comment that can be sent along with the request from your system. Maximum of 13 Characters.
     * @return mixed|string
     */
    public function STKPushSimulation($BusinessShortCode, $LipaNaMpesaPasskey, $TransactionType, $Amount, $PartyA, $PartyB, $PhoneNumber, $CallBackURL, $AccountReference, $TransactionDesc)
    {
        $url = $this->url . '/mpesa/stkpush/v1/processrequest';
        $timestamp = date("yymdhis");
        $password = $this->getPassword($BusinessShortCode, $LipaNaMpesaPasskey, $timestamp);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json', 'Authorization:Bearer ' . $this->access_token));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_HEADER, false);

        $curl_post_data = array(
            'BusinessShortCode' => $BusinessShortCode,
            'Password' => $password,
            'Timestamp' => $timestamp,
            'TransactionType' => $TransactionType,
            'Amount' => $Amount,
            'PartyA' => $PartyA,
            'PartyB' => $PartyB,
            'PhoneNumber' => $PhoneNumber,
            'CallBackURL' => $CallBackURL,
            'AccountReference' => $AccountReference,
            'TransactionDesc' => $TransactionType
        );

        $data_string = json_encode($curl_post_data);

        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);

        $curl_response = curl_exec($curl);
        $response = json_decode($curl_response);

        return $response;
    }

    /**
     * Use this function to initiate an STKPush Status Query request.
     * @param $CheckoutRequestID | Checkout RequestID
     * @param $BusinessShortCode | Business Short Code
     * @param $LipaNaMpesaPasskey | The Lipa Na MPESA passkey.  Get it from https://developer.safaricom.co.ke/test_credentials
     * @return mixed|string
     */
    public function STKPushQuery($CheckoutRequestID, $BusinessShortCode, $LipaNaMpesaPasskey)
    {
        $url =  $this->url . '/mpesa/stkpushquery/v1/query';
        $timestamp = date("yymdhis");
        $password = $this->getPassword($BusinessShortCode, $LipaNaMpesaPasskey, $timestamp);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json', 'Authorization:Bearer ' . $this->access_token));


        $curl_post_data = array(
            'BusinessShortCode' => $BusinessShortCode,
            'Password' => $password,
            'Timestamp' => $timestamp,
            'CheckoutRequestID' => $CheckoutRequestID
        );

        $data_string = json_encode($curl_post_data);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($curl, CURLOPT_HEADER, false);

        $curl_response = curl_exec($curl);
        $response = json_decode($curl_response);

        return $response;
    }

    /**
     *Use this function to confirm all transactions in callback routes
     */
    public function finishTransaction($status = true)
    {
        if ($status === true) {
            $resultArray = [
                "ResultDesc" => "Confirmation Service request accepted successfully",
                "ResultCode" => "0"
            ];
        } else {
            $resultArray = [
                "ResultDesc" => "Confirmation Service not accepted",
                "ResultCode" => "1"
            ];
        }

        header('Content-Type: application/json');

        echo json_encode($resultArray);
    }


    /**
     *Use this function to get callback data posted in callback routes
     */
    public function getDataFromCallback()
    {
        $callbackJSONData = file_get_contents('php://input');
        return $callbackJSONData;
    }
}
