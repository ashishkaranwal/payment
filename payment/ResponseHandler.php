<?php
require_once './paytm/PaytmGateway.php';
require_once './razorpay/RazorpayGateway.php';
require_once './paypal/PaypalGateway.php';
// Add other gateways similarly

class PaymentResponseHandler {
    private $gateway;

    public function __construct($gatewayName) {
        // Initialize the appropriate gateway response handler
        switch ($gatewayName) {
            case 'paytm':
                $this->gateway = new PaytmGateway();
                break;
            case 'razorpay':
                $this->gateway = new RazorpayGateway();
                 break;
            case 'paypal':
                $this->gateway = new PayPalGateway();
                 break;
            default:
                throw new Exception("Invalid Payment Gateway:".$gatewayName);
        }
    }

    public function handleResponse($params) {
        // Call the specific gateway's response handling method
        return $this->gateway->processResponse($params);
    }
}
