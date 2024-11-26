<?php
require_once 'vendor/autoload.php';


class CustomerValidator
{
    public function validate($customerData){
        if ($customerData['name'] == ''){
            die ('Customer name is required');
            return;
        }

        if($customerData['contact_info'] == ''){
            die ('Customer contact info is required');
            return;
        }
    }
}

class PaymentDataValidator
{
    public function validate($paymentData){
        if ($paymentData['source'] == ''){
            die('Payment source is required');
            return;
        }
    }
}

class Notifier
{
    public function sendConfirmation($customerData){
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
    }
}

class TransactionLogger{
    public function logTransaction($charge, $customerData){
        $transactionDetails = 'Transaction ID: '. $charge->id. ' Amount: '. $charge->amount. ' Status: '. $charge->status . ' Customer Name: '. $customerData['name']. PHP_EOL;
        file_put_contents('transaction.txt', $transactionDetails, FILE_APPEND);
    }
}


class PaymentService{
    private $customerValidator;
    private $paymentDataValidator;
    private $notifier;
    private $transactionLogger;

    public function __construct()
    {
        $this->customerValidator = new CustomerValidator();
        $this->paymentDataValidator = new PaymentDataValidator();
        $this->notifier = new Notifier();
        $this->transactionLogger = new TransactionLogger();
    }

    public function processTransaction($customerData, $paymentData){
        try{
            $this->customerValidator->validate($customerData);
            $this->paymentDataValidator->validate($paymentData);
        }
        catch (Exception $e){
            echo 'Validation failed' . $e->getMessage();
            return;
        }

        $stripe = new \Stripe\StripeClient('sk_test_51QLGoBGPaAS5c1AcKxfJICZ651I3A9fDHGWX7bkceKI9qJIcj9xZtH7BbbZYr2bfUxwxJIgsYj6GpGUKeZHqNyOU00j7Aj5v3k');

        try{
            $charge = $stripe->charges->create([
                'amount' => $paymentData['amount'],
                'currency' => 'usd',
                'source' => $paymentData['source'],
                'description' => 'Testing solid principles',
            ]);

            $this->notifier->sendConfirmation($customerData);
            $this->transactionLogger->logTransaction($charge, $customerData);
            return $charge;
        }
        catch (Exception $e){
            echo 'Payment failed' . $e->getMessage();
            return;
        }
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

$processor = new PaymentService();
$processor->processTransaction($paymentWithEmail, $paymentData);
$processor->processTransaction($paymentWithPhone, $paymentData);
?>