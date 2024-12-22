<?php
require_once './PaymentHandler.php';

try {
    // Retrieve payment details from GET request
    $gatewayName = $_GET['gateway']; // E.g., 'paytm', 'instamojo'
    $params = array(
        'ORDER_ID' => $_GET['ORDER_ID'],
        'CUST_ID' => $_GET['CUST_ID'],
        'INDUSTRY_TYPE_ID' => $_GET['INDUSTRY_TYPE_ID'],
        'CHANNEL_ID' => $_GET['CHANNEL_ID'],
        'TXN_AMOUNT' => $_GET['TXN_AMOUNT'],
        'PHN' => $_GET['PHN'],
        'EMAIL' => $_GET['EMAIL'],
        'CUST_NAME' => $_GET['CUST_NAME']
    );

    // Create payment handler and process payment
    $paymentHandler = new PaymentHandler($gatewayName);
    echo $paymentHandler->processPayment($params);

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
