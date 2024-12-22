<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once './paytm/PaytmGateway.php';
require_once './razorpay/RazorpayGateway.php';
require_once './paypal/PaypalGateway.php';

class PaymentHandler {
    private $gateway;

    public function __construct($gatewayName) {
        // Initialize the appropriate gateway based on the name
        switch ($gatewayName) {
            case 'paytm':
                $this->gateway = new PaytmGateway();
                break;
            case 'razorpay':
                $this->gateway = new RazorpayGateway();
                break;
            case 'paypal':
                $this->gateway = new PaypalGateway();
                break;
            default:
                throw new Exception("Invalid Payment Gateway");
        }
    }

    public function processPayment($params) {
        // Call the specific gateway's payment method
        return $this->gateway->pay($params);
    }
    
    public function checkPaymentStatus($orderID) {
        // Call the specific gateway's method to check payment status
        return $this->gateway->checkStatus($orderID);
    }
}
