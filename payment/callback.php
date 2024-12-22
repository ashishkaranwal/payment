<?php
require_once './ResponseHandler.php';

try {
    // Get the gateway name from request or session (you may store it during payment initiation)
    $gatewayName = $_GET['gateway']??$_POST['gateway']; // E.g., 'paytm', 'instamojo', etc.

    // Collect all response parameters
    $params = $_POST;

    // Create payment response handler
    $responseHandler = new PaymentResponseHandler($gatewayName);

    // Process the gateway response
    $response = $responseHandler->handleResponse($params);

    // Output the response (for debugging or redirecting to a confirmation page)
    echo "<pre>";
    print_r($response);
    echo "</pre>";

    // Redirect to success/failure page based on status
    // if ($response['status'] == 'TXN_SUCCESS') {
    //     header("Location: /success_page.php");
    // } else {
    //     header("Location: /failure_page.php");
    // }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
