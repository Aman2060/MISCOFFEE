# eSewa Payment Gateway Integration

This document describes the updated eSewa payment gateway integration for the MIS Coffee website, based on the latest eSewa ePay API documentation.

## Overview

The integration uses the latest eSewa ePay API v2 with proper HMAC SHA256 signature generation and transaction verification. The system supports both test and production environments.

## Files Updated

### 1. `esewa_helper.php` (NEW)
- **Purpose**: Helper class for eSewa payment integration
- **Features**:
  - HMAC SHA256 signature generation
  - Payment form data generation
  - Transaction status verification
  - Parameter validation
  - Test and production mode support

### 2. `esewa_payment.php` (UPDATED)
- **Purpose**: Payment processing page
- **Updates**:
  - Uses `EsewaHelper` class for cleaner code
  - Proper signature generation
  - All required eSewa parameters included
  - Debug information for testing

### 3. `payment_success.php` (UPDATED)
- **Purpose**: Payment success callback page
- **Updates**:
  - Uses new eSewa status check API
  - Fallback to old verification method
  - Enhanced transaction details display
  - Better error handling

### 4. `test_esewa.php` (NEW)
- **Purpose**: Test file to verify integration
- **Features**:
  - Tests all helper functions
  - Validates signature generation
  - Tests API connectivity
  - Sample payment form generation

## API Integration Details

### Test Credentials
```php
Product Code: EPAYTEST
Secret Key: 8gBm/:&EnhH.1/q(
Payment URL: https://rc-epay.esewa.com.np/api/epay/main/v2/form
Status URL: https://rc.esewa.com.np/api/epay/transaction/status/
```

### Production Credentials
```php
Product Code: YOUR_PRODUCTION_PRODUCT_CODE
Secret Key: YOUR_PRODUCTION_SECRET_KEY
Payment URL: https://epay.esewa.com.np/api/epay/main/v2/form
Status URL: https://epay.esewa.com.np/api/epay/transaction/status/
```

## Required Parameters

### Payment Form Parameters
- `amount` - Base amount
- `tax_amount` - Tax amount
- `product_service_charge` - Service charge
- `product_delivery_charge` - Delivery charge
- `total_amount` - Total amount (sum of all charges)
- `transaction_uuid` - Unique transaction identifier
- `product_code` - eSewa product code
- `success_url` - Success callback URL
- `failure_url` - Failure callback URL
- `signed_field_names` - Fields included in signature
- `signature` - HMAC SHA256 signature

### Signature Generation
The signature is generated using HMAC SHA256 with the following format:
```
signature_data = "total_amount={total_amount},transaction_uuid={transaction_uuid},product_code={product_code}"
signature = base64_encode(hash_hmac('sha256', signature_data, secret_key, true))
```

## Transaction Flow

1. **Payment Initiation**
   - User selects items and proceeds to payment
   - System generates unique transaction UUID
   - Payment form data is created with signature
   - User is redirected to eSewa payment page

2. **Payment Processing**
   - User completes payment on eSewa
   - eSewa redirects to success/failure URL
   - System receives transaction parameters

3. **Transaction Verification**
   - System calls eSewa status check API
   - Verifies transaction status and amount
   - Updates order status accordingly
   - Clears cart on successful payment

## Status Check API

The system uses the new eSewa status check API:
```
GET https://rc.esewa.com.np/api/epay/transaction/status/?product_code=EPAYTEST&total_amount=100&transaction_uuid=123
```

### Response Statuses
- `COMPLETE` - Payment successful
- `PENDING` - Payment initiated but not completed
- `FAILED` - Payment failed
- `CANCELED` - Payment canceled
- `NOT_FOUND` - Transaction not found
- `AMBIGUOUS` - Payment in halt state

## Usage Examples

### Basic Payment Integration
```php
require_once 'esewa_helper.php';

$esewa = new EsewaHelper(true); // true for test mode

$payment_data = $esewa->generatePaymentData(
    100.00, // amount
    0, // tax_amount
    0, // service_charge
    0, // delivery_charge
    'http://localhost/success.php',
    'http://localhost/failure.php'
);

$form_data = $payment_data['form_data'];
$transaction_uuid = $payment_data['transaction_uuid'];
```

### Transaction Verification
```php
$transaction_details = $esewa->getTransactionDetails($transaction_uuid, $total_amount);

if ($transaction_details['success'] && $transaction_details['status'] === 'COMPLETE') {
    // Payment successful
    // Process order
} else {
    // Payment failed
    // Handle error
}
```

## Testing

### Run Integration Tests
1. Access `test_esewa.php` in your browser
2. Review test results for each component
3. Verify signature generation
4. Test API connectivity

### Test Payment Flow
1. Add items to cart
2. Proceed to payment page
3. Use demo payment for testing
4. Verify transaction verification

### eSewa Test Credentials
- **eSewa ID**: 9806800001/2/3/4/5
- **Password**: Nepal@123
- **MPIN**: 1122 (for mobile app)
- **Token**: 123456

## Security Considerations

1. **Signature Verification**: Always verify signatures on both request and response
2. **Amount Validation**: Verify transaction amounts match expected values
3. **Transaction UUID**: Use unique, non-predictable transaction IDs
4. **HTTPS**: Use HTTPS for all payment-related communications
5. **Secret Key**: Keep secret keys secure and never expose them in client-side code

## Error Handling

The system includes comprehensive error handling:
- API connection failures
- Invalid responses
- Signature verification failures
- Transaction status errors
- Fallback verification methods

## Production Deployment

Before deploying to production:

1. **Update Credentials**: Replace test credentials with production credentials
2. **Update URLs**: Change URLs to production endpoints
3. **Remove Debug Info**: Remove debug information from payment pages
4. **SSL Certificate**: Ensure HTTPS is properly configured
5. **Error Logging**: Implement proper error logging
6. **Monitoring**: Set up transaction monitoring

## Troubleshooting

### Common Issues

1. **Signature Mismatch**
   - Verify secret key is correct
   - Check parameter order in signature generation
   - Ensure all required fields are included

2. **Transaction Not Found**
   - Verify transaction UUID format
   - Check if transaction exists in eSewa system
   - Ensure proper time synchronization

3. **API Connection Issues**
   - Check network connectivity
   - Verify API endpoints are accessible
   - Review firewall settings

4. **Payment Verification Failures**
   - Check amount formatting
   - Verify product code
   - Review callback URLs

### Debug Information

Enable debug mode by setting:
```php
$esewa = new EsewaHelper(true); // true enables debug mode
```

Debug information includes:
- Transaction parameters
- Generated signatures
- API responses
- Error details

## Support

For eSewa integration support:
- **eSewa Documentation**: https://developer.esewa.com.np/
- **eSewa Support**: Contact eSewa technical support
- **Test Environment**: Use test credentials for development

## Changelog

### Version 2.0 (Current)
- Updated to latest eSewa ePay API v2
- Implemented proper HMAC SHA256 signature generation
- Added transaction status check API
- Created modular helper class
- Enhanced error handling and validation
- Added comprehensive testing framework

### Version 1.0 (Previous)
- Basic eSewa integration
- Simple form submission
- Basic transaction verification 