<?php

 function updateOrderStatus($orderID, $status, $message,$txnID) {
        
    $url = "https://us-central1-akshita-astro-tarot.cloudfunctions.net/handlePaymentCallback"; // Cloud Function URL

    $data = array(
        'txnStatus' => $status,    // Transaction status
        'txnIdLocal' => 'someLocalTxnId', // You can pass this dynamically if available
        'txnIdPg' => $txnID, // Pass payment gateway txn ID dynamically if available
        'orderStatus' => $status,  // Order status
        'orderId' => $orderID      // The order ID
    );

    // Initialize cURL
    $ch = curl_init($url);

    // Convert the data array to JSON
    $payload = json_encode($data);

    // Set cURL options
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

    // Set the content type to application/json
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Content-Length: ' . strlen($payload))
    );

    // Execute the request and get the response
    $response = curl_exec($ch);

    // Check for errors
    if ($response === false) {
        $error = curl_error($ch);
        curl_close($ch);
        throw new Exception("cURL Error: " . $error);
    }

    // Close cURL session
    curl_close($ch);

    // Return or process the response
    return json_decode($response, true); // Decode JSON response to PHP array
}