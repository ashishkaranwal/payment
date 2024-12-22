<?php
require_once("./functions.php");

// Retrieve the form data from the POST request
$order_id = isset($_POST['order']) ? $_POST['order'] : null;
$payment_id = isset($_POST['payment_id']) ? $_POST['payment_id'] : null;

// Check if the required order ID is available
if ($order_id) {
    // Update the order status in the database to 'Failed'
    updateOrderStatus($order_id, 'Failed', "Cancelled by user.", $payment_id);

    // Optionally, redirect the user to a "payment failed" or "cancelled" page
   // header("Location: /payment-failed.html"); // You can replace this URL with your actual payment failure page.
    exit();
} else {
    echo "Error: Invalid order ID.";
}
