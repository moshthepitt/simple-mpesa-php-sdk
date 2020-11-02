# Really Simple M-PESA API PHP SDK

This is a really simple PHP implementation of the [Safaricom M-PESA API](https://developer.safaricom.co.ke/docs).   The intention is to have the entire thing implemented in just one PHP file (because why not) that is hopefully easy to work with and ultra portable (just copy paste).

## Implemented APIs

### 1. M-PESA Express (aka STK Push)

For Lipa Na M-Pesa payments using STK Push.

#### M-PESA Express Request

First, you need to [start a transaction](https://developer.safaricom.co.ke/lipa-na-m-pesa-online/apis/post/stkpush/v1/processrequest), like this:

```php
// instantiate the API object
$api = new Mpesa('YOUR CONSUMER KEY', 'YOUR CONSUMER SECRET');
// generate an access token
$api->generateToken();
// make the STK push request
print_r($api->STKPushSimulation(
    000000,  // the till number (or shortcode). Get tests ones from https://developer.safaricom.co.ke/faqs/what-shortcode-do-you-use
    'YOUR PASSKEY', // your passkey
    'CustomerPayBillOnline', // either CustomerPayBillOnline or CustomerBuyGoodsOnline
    1337,  // the amount
    '254xxxxxxxxx', // The phone number sending money
    174379,  // the till number
    '254xxxxxxxxx', // The Mobile Number to receive the STK Pin Prompt.
    'http://example.com/callback.php', // your callback URL
    'account-123', // account reference
    'description' // the description
));
```

#### M-PESA Express Query

After you have initiated the payment, you can [query for a status](https://developer.safaricom.co.ke/lipa-na-m-pesa-online/apis/post/stkpushquery/v1/query) as follows:

```php
// instantiate the API object
$api = new Mpesa('YOUR CONSUMER KEY', 'YOUR CONSUMER SECRET');
// generate an access token
$api->generateToken();
// make the STK push query request
print_r($api->STKPushQuery(
    'Safaricom transaction id aka $CheckoutRequestID',  // the transaction id that you get from Safaricom from the 'M-PESA Express Request' above
    174379,  // the till number
    'your passkey', // your passkey
));
```

#### Callbacks

You will notice that the `STKPushSimulation` request includes a callback url (`CallBackURL`) parameter.

Safaricom will use this to send transaction notifications to your web service.

There are two included utility methods for dealing with these:

##### getDataFromCallback

Use this to parse the Safaricom request so that you can use the information therein.

##### finishTransaction

Use this to generate a response to the Safaricom request.  Please note that if you do not provide a valid response the M-PESA transaction may not go through.