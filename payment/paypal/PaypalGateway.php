<?php
require_once("./functions.php");

class PayPalGateway {
    public function pay($params) {
        
            $orderID = $params['ORDER_ID'];
            $status = $params['STATUS'];
            $responseMsg = $params['RESPMSG'];
            $txnID=$params['ORDERID'];
            
            // Handle transaction status
            if ($status == "TXN_SUCCESS") {
                // Transaction success
                updateOrderStatus($orderID, 'Received', $responseMsg,$txnID);
            } elseif ($status == "Failed") {
                // Transaction failed
                updateOrderStatus($orderID, 'Failed', $responseMsg,$txnID);
            } else {
                // Transaction failure
               updateOrderStatus($orderID, $status, $responseMsg,$txnID);
            }
    }
    
    
    
    
    
  public function checkStatus($orderID) {
    $accessToken = $this->getAccessToken();

    // Set the PayPal API URL for order status check
    $url = "https://api-m.sandbox.paypal.com/v2/checkout/orders/{$orderID}";

    // Make the GET request to PayPal to fetch the order status
    $response = $this->makeGetRequest($url, $accessToken);

    $responseDecoded = json_decode($response, true);

    if ($responseDecoded && isset($responseDecoded['status'])) {
        return [
            'status' => $responseDecoded['status'],  // Example: "COMPLETED", "PENDING", etc.
            'details' => $responseDecoded
        ];
    } else {
        return [
            'status' => 'FAILURE',
            'message' => 'Unable to fetch status',
            'details' => $responseDecoded
        ];
    }
}

private function getAccessToken() {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api-m.sandbox.paypal.com/v1/oauth2/token');
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, $this->clientId . ":" . $this->clientSecret);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");

    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if (empty($result)) {
        die("Error: No response from PayPal. HTTP Code: " . $httpCode);
    }

    $json = json_decode($result);
    return $json->access_token;
}

    private function makePostRequest($url, $params) {
        $postData = json_encode($params);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($postData)
        ));

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }
}
?>
