<?php
require_once './PaymentHandler.php';

if (isset($_GET['gateway']) && isset($_GET['ORDER_ID'])) {
    $gatewayName = $_GET['gateway'];
    $orderID = $_GET['ORDER_ID'];

    try {
        // Instantiate the PaymentHandler with the chosen gateway
        $paymentHandler = new PaymentHandler($gatewayName);

        // Call the checkPaymentStatus method and get the result
        $statusResponse = $paymentHandler->checkPaymentStatus($orderID);

        // Return the response as JSON
        header('Content-Type: application/json');
        echo json_encode($statusResponse);

    } catch (Exception $e) {
        // Handle errors (e.g., invalid gateway)
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'FAILURE',
            'message' => $e->getMessage()
        ]);
    }
} else {
    // Handle missing parameters
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'FAILURE',
        'message' => 'Missing required parameters (gateway or ORDER_ID)'
    ]);
}
?>
