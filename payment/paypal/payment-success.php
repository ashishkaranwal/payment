<?php
require_once './PayPalGateway.php';

$gateway = new PayPalGateway();
$params = [
    'paymentId' => $_GET['paymentId'],
    'PayerID' => $_GET['PayerID'],
    'orderID' => $_GET['orderID']
];

$gateway->processResponse($params);
?>
