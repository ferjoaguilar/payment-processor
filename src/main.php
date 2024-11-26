<?php
require_once 'vendor/autoload.php';

class PaymentProcessor
{
    public function processTransaction($customerData, $paymentData)
    {
        if ($customerData['name'] == ''){
            echo 'Customer name is required';
            return;
        }

        if($customerData['contact_info'] == ''){
            echo 'Customer contact info is required';
            return;
        }

        if ($paymentData['source'] == ''){
            echo 'Payment source is required';
            return;
        }

        $stripe = new \Stripe\StripeClient('MY_SECRET_KEY');

        try{
            $charge = $stripe->charges->create([
                'amount' => $paymentData['amount'],
                'currency' => 'usd',
                'source' => $paymentData['source'],
                'description' => 'My First Test Charge (created for API docs)',
            ]);
        }
        catch (Exception $e){
            echo 'Payment failed';
            return;
        }

        if (array_key_exists('email', $customerData['contact_info'])){
            $email = $customerData['contact_info']['email'];
            $subject = 'Payment Successful';
            $message = 'Your payment was successful';
            //mail($email, $subject, $message);
            echo 'Email sent'. $customerData['contact_info']['email'];
        } else if (array_key_exists('phone', $customerData['contact_info'])){
            $phone = $customerData['contact_info']['phone'];
            $message = 'Your payment was successful';
            $sms_gateway = 'my-sms-gateway';
            //sendSMS($phone, $message);
            echo 'SMS sent'. $customerData['contact_info']['phone']. 'using'. $sms_gateway;
        } else {
            echo 'No valid contact information for notification';
            return;
        }

        $transactionDetails = 'Transaction ID: '. $charge->id. ' Amount: '. $charge->amount. ' Status: '. $charge->status . ' Customer Name: '. $customerData['name']. PHP_EOL;
        file_put_contents('transaction.txt', $transactionDetails, FILE_APPEND);

    }
}

$paymentWithEmail = [
    'name' => 'User 1',
    'contact_info' => [
        'email' => 'testing123@gmil.com'
    ]
];

$paymentWithPhone = [
    'name' => 'User 2',
    'contact_info' => [
        'phone' => '1234567890'
    ]
];

$paymentData = [
    'amount' => 2000,
    'source' => 'tok_visa'
];

$processor = new PaymentProcessor();
$processor->processTransaction($paymentWithEmail, $paymentData);
$processor->processTransaction($paymentWithPhone, $paymentData);
?>