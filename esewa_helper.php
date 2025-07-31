<?php
/**
 * eSewa ePay Helper Functions
 * Based on the latest eSewa ePay API documentation
 * https://developer.esewa.com.np/pages/Epay#transactionflow
 */

class EsewaHelper {
    // Test credentials from eSewa documentation
    const TEST_PRODUCT_CODE = "EPAYTEST";
    const TEST_SECRET_KEY = "8gBm/:&EnhH.1/q";
    
    // Production credentials (replace with your actual credentials)
    const PROD_PRODUCT_CODE = "YOUR_PRODUCTION_PRODUCT_CODE";
    const PROD_SECRET_KEY = "YOUR_PRODUCTION_SECRET_KEY";
    
    // URLs
    const TEST_PAYMENT_URL = "https://rc-epay.esewa.com.np/api/epay/main/v2/form";
    const PROD_PAYMENT_URL = "https://epay.esewa.com.np/api/epay/main/v2/form";
    const TEST_STATUS_URL = "https://rc.esewa.com.np/api/epay/transaction/status/";
    const PROD_STATUS_URL = "https://epay.esewa.com.np/api/epay/transaction/status/";
    
    private $is_test_mode;
    private $product_code;
    private $secret_key;
    private $payment_url;
    private $status_url;
    
    public function __construct($test_mode = true) {
        $this->is_test_mode = $test_mode;
        
        if ($test_mode) {
            $this->product_code = self::TEST_PRODUCT_CODE;
            $this->secret_key = self::TEST_SECRET_KEY;
            $this->payment_url = self::TEST_PAYMENT_URL;
            $this->status_url = self::TEST_STATUS_URL;
        } else {
            $this->product_code = self::PROD_PRODUCT_CODE;
            $this->secret_key = self::PROD_SECRET_KEY;
            $this->payment_url = self::PROD_PAYMENT_URL;
            $this->status_url = self::PROD_STATUS_URL;
        }
    }
    
    /**
     * Generate HMAC SHA256 signature for eSewa payment
     * @param array $params Payment parameters
     * @return string Base64 encoded signature
     */
    public function generateSignature($params) {
        // Get the signed field names from parameters
        $signed_field_names = $params['signed_field_names'];
        $field_names = explode(',', $signed_field_names);
        
        // Build signature data string in the exact order specified
        $signature_parts = [];
        foreach ($field_names as $field_name) {
            $field_name = trim($field_name);
            if (isset($params[$field_name])) {
                $signature_parts[] = $field_name . "=" . $params[$field_name];
            }
        }
        
        $signature_data = implode(',', $signature_parts);
        
        // Generate HMAC SHA256 signature
        return base64_encode(hash_hmac('sha256', $signature_data, $this->secret_key, true));
    }
    
    /**
     * Generate HMAC SHA256 signature for eSewa payment (Alternative method)
     * This method follows the exact eSewa documentation format
     * @param array $params Payment parameters
     * @return string Base64 encoded signature
     */
    public function generateSignatureAlternative($params) {
        // According to eSewa documentation, the signature should be generated from:
        // total_amount,transaction_uuid,product_code
        $signature_data = "total_amount=" . $params['total_amount'] . 
                         ",transaction_uuid=" . $params['transaction_uuid'] . 
                         ",product_code=" . $params['product_code'];
        
        return base64_encode(hash_hmac('sha256', $signature_data, $this->secret_key, true));
    }
    
    /**
     * Verify signature received from eSewa
     * @param array $params Parameters received from eSewa
     * @param string $received_signature Signature received from eSewa
     * @return bool True if signature is valid
     */
    public function verifySignature($params, $received_signature) {
        $calculated_signature = $this->generateSignature($params);
        return hash_equals($calculated_signature, $received_signature);
    }
    
    /**
     * Generate payment form data
     * @param float $amount Base amount
     * @param float $tax_amount Tax amount
     * @param float $service_charge Service charge
     * @param float $delivery_charge Delivery charge
     * @param string $success_url Success callback URL
     * @param string $failure_url Failure callback URL
     * @return array Payment form data
     */
    public function generatePaymentData($amount, $tax_amount = 0, $service_charge = 0, $delivery_charge = 0, $success_url, $failure_url) {
        $transaction_uuid = 'TXN_' . time() . '_' . rand(1000, 9999);
        $total_amount = $amount + $tax_amount + $service_charge + $delivery_charge;
        
        $params = [
            'amount' => $amount,
            'tax_amount' => $tax_amount,
            'product_service_charge' => $service_charge,
            'product_delivery_charge' => $delivery_charge,
            'total_amount' => $total_amount,
            'transaction_uuid' => $transaction_uuid,
            'product_code' => $this->product_code,
            'success_url' => $success_url,
            'failure_url' => $failure_url,
            'signed_field_names' => "total_amount,transaction_uuid,product_code"
        ];
        
        $params['signature'] = $this->generateSignature($params);
        
        return [
            'form_data' => $params,
            'payment_url' => $this->payment_url,
            'transaction_uuid' => $transaction_uuid
        ];
    }
    
    /**
     * Check transaction status using eSewa status check API
     * @param string $transaction_uuid Transaction UUID
     * @param float $total_amount Total amount
     * @return array Status response
     */
    public function checkTransactionStatus($transaction_uuid, $total_amount) {
        $status_params = [
            'product_code' => $this->product_code,
            'total_amount' => $total_amount,
            'transaction_uuid' => $transaction_uuid
        ];
        
        $status_url = $this->status_url . '?' . http_build_query($status_params);
        
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $status_url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_USERAGENT, 'MIS Coffee Payment System');
        
        $response = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        curl_close($curl);
        
        if ($error) {
            return [
                'success' => false,
                'error' => 'CURL Error: ' . $error,
                'http_code' => $http_code
            ];
        }
        
        $response_data = json_decode($response, true);
        
        if (!$response_data) {
            return [
                'success' => false,
                'error' => 'Invalid JSON response',
                'response' => $response,
                'http_code' => $http_code
            ];
        }
        
        return [
            'success' => true,
            'data' => $response_data,
            'http_code' => $http_code
        ];
    }
    
    /**
     * Verify if transaction is successful
     * @param string $transaction_uuid Transaction UUID
     * @param float $total_amount Total amount
     * @return bool True if successful
     */
    public function isTransactionSuccessful($transaction_uuid, $total_amount) {
        $status = $this->checkTransactionStatus($transaction_uuid, $total_amount);
        
        if (!$status['success']) {
            return false;
        }
        
        $data = $status['data'];
        return isset($data['status']) && $data['status'] === 'COMPLETE';
    }
    
    /**
     * Get transaction details
     * @param string $transaction_uuid Transaction UUID
     * @param float $total_amount Total amount
     * @return array Transaction details
     */
    public function getTransactionDetails($transaction_uuid, $total_amount) {
        $status = $this->checkTransactionStatus($transaction_uuid, $total_amount);
        
        if (!$status['success']) {
            return [
                'success' => false,
                'error' => $status['error']
            ];
        }
        
        $data = $status['data'];
        
        return [
            'success' => true,
            'status' => $data['status'] ?? 'UNKNOWN',
            'ref_id' => $data['ref_id'] ?? null,
            'product_code' => $data['product_code'] ?? null,
            'total_amount' => $data['total_amount'] ?? null,
            'transaction_uuid' => $data['transaction_uuid'] ?? null
        ];
    }
    
    /**
     * Get test credentials for development
     * @return array Test credentials
     */
    public static function getTestCredentials() {
        return [
            'product_code' => self::TEST_PRODUCT_CODE,
            'secret_key' => self::TEST_SECRET_KEY,
            'payment_url' => self::TEST_PAYMENT_URL,
            'status_url' => self::TEST_STATUS_URL
        ];
    }
    
    /**
     * Validate payment parameters
     * @param array $params Payment parameters
     * @return array Validation result
     */
    public function validatePaymentParams($params) {
        $required_fields = [
            'amount', 'total_amount', 'transaction_uuid', 
            'product_code', 'success_url', 'failure_url'
        ];
        
        $errors = [];
        
        foreach ($required_fields as $field) {
            if (!isset($params[$field]) || empty($params[$field])) {
                $errors[] = "Missing required field: {$field}";
            }
        }
        
        if (!is_numeric($params['amount']) || $params['amount'] <= 0) {
            $errors[] = "Invalid amount";
        }
        
        if (!is_numeric($params['total_amount']) || $params['total_amount'] <= 0) {
            $errors[] = "Invalid total amount";
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
}

// Example usage:
/*
$esewa = new EsewaHelper(true); // true for test mode

// Generate payment data
$payment_data = $esewa->generatePaymentData(
    100.00, // amount
    0, // tax_amount
    0, // service_charge
    0, // delivery_charge
    'http://localhost/MISCOFFEE/payment_success.php',
    'http://localhost/MISCOFFEE/payment_failed.php'
);

// Check transaction status
$status = $esewa->checkTransactionStatus('TXN_1234567890', 100.00);
*/
?> 