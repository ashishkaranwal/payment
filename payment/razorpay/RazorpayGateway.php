<?php

require_once('./razorpay/razorpay-php-master/Razorpay.php');
require_once("./functions.php");


class RazorpayGateway {
    
    private $api_key = 'rzp_live_lkMveApriTY4M8ss';
    private $api_secret = 'r7VaXc3fY9vvk1s5zbNP1kjssf';  // Fixed missing string end
    private $api;

    public function __construct() {
        // Initialize the Razorpay API object using the api key and secret
        $this->api = new Razorpay\Api\Api($this->api_key, $this->api_secret);
    }
    
    public function pay($params) {
        $CUST_NAME = $params["CUST_NAME"];
        $ORDER_ID = $params["ORDER_ID"];
        $CUST_ID = $params["CUST_ID"];
        $TXN_AMOUNT = $params["TXN_AMOUNT"];
        $PHN_NUMBER = $params["PHN"];
        $MAIL = $params["EMAIL"];

        $currency = 'INR';
        $receipt = 'order_receipt_' . $ORDER_ID; // Unique identifier for the order
        $payment_capture = 1; // Auto capture payment

        // Error handling for order creation
        try {
            $order = $this->api->order->create(array(
                'amount' => $TXN_AMOUNT * 100, // amount in paise
                'currency' => $currency,
                'receipt' => $receipt,
                'payment_capture' => $payment_capture
            ));
        } catch (\Exception $e) {
            die('Error: ' . $e->getMessage());
        }

        $order_id = $order->id;
        $order_amount = $order->amount;

        // Render Razorpay payment form
        echo '
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Razorpay Payment</title>
            
            <style>
       html, body {
    height: 100%; /* Ensures that the body takes the full height of the viewport */
    margin: 0; /* Removes default margins */
    padding: 0; /* Removes default padding */
}

body {
    background-image: url("./background.jpg"); /* Replace with the correct path */
    background-size: cover; /* Ensures the image covers the entire body */
    background-repeat: no-repeat; /* Prevents the background image from repeating */
    background-position: center; /* Centers the background image */
    background-attachment: fixed; /* Keeps the background fixed while scrolling */
}

    </style>
        </head>
        <body>

        <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
        <script>

       function redirectToFailedPaymentPage(orderid, paymentId = null) {
         var form = document.createElement("form");
          form.method = "POST";
           form.action = "./cancel.php"; // Ensure the path is correct for your environment

    var addField = function (name, value) {
        var input = document.createElement("input");
        input.type = "hidden";
        input.name = name;
        input.value = value;
        form.appendChild(input);
    };

    // Add order and payment ID to the form
    addField("order", orderid);
    if (paymentId) {
        addField("payment_id", paymentId);
    }

    document.body.appendChild(form);
    form.submit();
}


        var options = {
            "key": "' . $this->api_key . '",
            "amount": "' . $order_amount . '",
            "currency": "' . $currency . '",
            "name": "Akshita Astrotarot",
            "description": "Purchase Description",
            "image": "../../assets/logo.png",
            "order_id": "' . $order_id . '",
            "handler": function (response) {
                var form = document.createElement("form");
                form.method = "POST";
                form.action = "./callback.php?gateway=razorpay"; 

                var addField = function (name, value) {
                    var input = document.createElement("input");
                    input.type = "hidden";
                    input.name = name;
                    input.value = value;
                    form.appendChild(input);
                };

                addField("razorpay_order_id", response.razorpay_order_id);
                addField("razorpay_payment_id", response.razorpay_payment_id);
                addField("razorpay_signature", response.razorpay_signature);
                addField("status_code", response.status_code);
                addField("gateway", "razorpay");
                addField("ORDERID", "' . $ORDER_ID . '");

                document.body.appendChild(form);
                form.submit();
            },
            "prefill": {
                "name": "' . $CUST_NAME . '",
                "email": "' . $MAIL . '",
                "phone": "' . $PHN_NUMBER . '"
            },
            "theme": {
                "color": "#1e1e27"
            },
            "modal": {
                "ondismiss": function () {
                    redirectToFailedPaymentPage("' . $ORDER_ID . '","' . $order_id . '");
                }
            }
        };

        var rzp = new Razorpay(options);
        rzp.open();

        </script>

        </body>
        </html>';
    }
    
    public function processResponse($params) {
        $success = true;
        $error = null;
        $payment_id = '';

        try {
            // Verify the Razorpay signature to ensure the response is authentic
            $attributes = array(
                'razorpay_order_id' => $params['razorpay_order_id'],
                'razorpay_payment_id' => $params['razorpay_payment_id'],
                'razorpay_signature' => $params['razorpay_signature'],
                'status_code' => $params['status_code']
            );

            $this->api->utility->verifyPaymentSignature($attributes);

            // The payment signature is valid, extract payment details
            $payment_id = $params['razorpay_payment_id'];

            // You can update your database or perform other tasks based on the payment status
            // For example, check the payment status
            $payment = $this->api->payment->fetch($payment_id);

            if ($payment->status === 'captured') {
                // Payment successful
                $order = $params['ORDERID'];
                $msg = $payment->status;
                
                updateOrderStatus($order, 'Received', $msg,$payment_id);
            } else if($payment->status ==='failed'){
                // Payment failed
                $success = false;
                $order = $params['ORDERID'];
                $msg = $payment->status;
                
                updateOrderStatus($order, 'Failed', $msg,$payment_id);
            }else{
                // Payment failed
                $success = false;
                $order = $params['ORDERID'];
                $msg = $payment->status;
                
                updateOrderStatus($order, $msg, $msg,$payment_id);
            }
        } catch (\Razorpay\Api\Errors\SignatureVerificationError $e) {
            // Signature verification failed
            $success = false;
            $error = 'Signature Verification Failed: ' . $e->getMessage();
            $payment_id = $params['razorpay_payment_id'];
            $order = $params['ORDERID'];
            updateOrderStatus($order, 'Failed', $error,$payment_id);
        }

    }
    
    // New method to check payment status
    public function checkStatus($orderId) {
        try {
            // Fetch the payment details using the order ID
            $payment = $this->api->payment->fetch($orderId);
            return [
                'status' => $payment->status,
                'details' => $payment
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'FAILURE',
                'message' => $e->getMessage()
            ];
        }
    }
}
