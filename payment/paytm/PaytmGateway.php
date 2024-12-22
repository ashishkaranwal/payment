<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once("./paytm/lib/config_paytm.php");
require_once("./paytm/lib/encdec_paytm.php");
require_once("./functions.php");

class PaytmGateway {
    public function pay($params) {
        $paramList = array(
            "MID" => PAYTM_MERCHANT_MID,
            "ORDER_ID" => $params["ORDER_ID"],
            "CUST_ID" => $params["CUST_ID"],
            "INDUSTRY_TYPE_ID" => $params["INDUSTRY_TYPE_ID"],
            "CHANNEL_ID" => $params["CHANNEL_ID"],
            "TXN_AMOUNT" => $params["TXN_AMOUNT"],
            "WEBSITE" => PAYTM_MERCHANT_WEBSITE,
            "CALLBACK_URL" => "https://api.akshitaastrotarot.com/payment/callback.php?gateway=paytm",
            "MSISDN" => $params["PHN"],
            "EMAIL" => $params["EMAIL"],
            "VERIFIED_BY" => "EMAIL",
            "IS_USER_VERIFIED" => "YES"
        );

        // Generate checksum
        $checkSum = getChecksumFromArray($paramList, PAYTM_MERCHANT_KEY);
        
        // Generate the form HTML with hidden inputs and submit script
        $form = '<form method="post" action="' . PAYTM_TXN_URL . '" name="f1">';
        foreach ($paramList as $name => $value) {
            $form .= '<input type="hidden" name="' . $name . '" value="' . $value . '">';
        }
        $form .= '<input type="hidden" name="CHECKSUMHASH" value="' . $checkSum . '">';
        $form .= '</form>';
        $form .= '<script type="text/javascript">document.f1.submit();</script>';

        return $form;
    }
    
    
     public function processResponse($params) {
        $paytmChecksum = isset($params["CHECKSUMHASH"]) ? $params["CHECKSUMHASH"] : ""; 
        $isValidChecksum = verifychecksum_e($params, PAYTM_MERCHANT_KEY, $paytmChecksum); 

        if($isValidChecksum == "TRUE") {
            $orderID = $params['ORDERID'];
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

            // return [
            //     'status' => $status,
            //     'message' => $responseMsg,
            //     'details' => $params
            // ];
        } else {
            // return [
            //     'status' => 'FAILURE',
            //     'message' => 'Checksum mismatched',
            //     'details' => $params
            // ];
             updateOrderStatus($orderID, 'Failed', $responseMsg);
        }
    }
    
    
    
      public function checkStatus($orderID) {
        $paramList = array(
            "MID" => PAYTM_MERCHANT_MID,
            "ORDERID" => $orderID
        );

        $checkSum = getChecksumFromArray($paramList, PAYTM_MERCHANT_KEY);

        $paramList["CHECKSUMHASH"] = $checkSum;

        $url = "https://securegw.paytm.in/merchant-status/getTxnStatus";

        $response = $this->makePostRequest($url, $paramList);
        $responseDecoded = json_decode($response, true);

        if ($responseDecoded && isset($responseDecoded['STATUS'])) {
            return [
                'status' => $responseDecoded['STATUS'],
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
