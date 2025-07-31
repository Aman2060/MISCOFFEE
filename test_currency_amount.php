<?php
/**
 * Test Currency and Amount Issues
 * This file tests if currency mismatch and amount issues are causing the 400 error
 */

echo "<h1>Currency and Amount Test</h1>";

// Test different currency and amount scenarios
$test_scenarios = [
    [
        'name' => 'USD Amount (Current Issue)',
        'amount' => 22,
        'currency' => 'USD',
        'description' => 'This is what you\'re currently using - USD amounts'
    ],
    [
        'name' => 'NPR Amount (Correct)',
        'amount' => 2200, // 22 USD ≈ 2200 NPR
        'currency' => 'NPR',
        'description' => 'eSewa expects Nepalese Rupees'
    ],
    [
        'name' => 'Small NPR Amount',
        'amount' => 100,
        'currency' => 'NPR',
        'description' => 'Small amount in NPR'
    ],
    [
        'name' => 'Large NPR Amount',
        'amount' => 5000,
        'currency' => 'NPR',
        'description' => 'Larger amount in NPR'
    ],
    [
        'name' => 'Integer Amount',
        'amount' => 100,
        'currency' => 'NPR',
        'description' => 'Integer amount (no decimals)'
    ],
    [
        'name' => 'Decimal Amount',
        'amount' => 100.50,
        'currency' => 'NPR',
        'description' => 'Decimal amount'
    ]
];

$secret_key = "8gBm/:&EnhH.1/q";

echo "<h2>Analysis</h2>";
echo "<p><strong>Issue:</strong> You're using USD amounts ($22) but eSewa expects Nepalese Rupees (NPR)</p>";
echo "<p><strong>Solution:</strong> Convert USD to NPR or use NPR amounts directly</p>";
echo "<p><strong>Exchange Rate:</strong> 1 USD ≈ 100 NPR (approximate)</p>";

echo "<h2>Test Scenarios</h2>";

foreach ($test_scenarios as $index => $scenario) {
    echo "<h3>Scenario " . ($index + 1) . ": " . $scenario['name'] . "</h3>";
    echo "<p><strong>Description:</strong> " . $scenario['description'] . "</p>";
    echo "<p><strong>Amount:</strong> " . $scenario['amount'] . " " . $scenario['currency'] . "</p>";
    
    // Generate signature
    $transaction_uuid = 'TXN_' . time() . '_' . ($index + 1);
    $signature_data = "total_amount=" . $scenario['amount'] . ",transaction_uuid=" . $transaction_uuid . ",product_code=EPAYTEST";
    $signature = base64_encode(hash_hmac('sha256', $signature_data, $secret_key, true));
    
    echo "<p><strong>Signature Data:</strong> " . $signature_data . "</p>";
    echo "<p><strong>Signature:</strong> " . substr($signature, 0, 20) . "...</p>";
    
    // Generate test form
    echo "<form action='https://rc-epay.esewa.com.np/api/epay/main/v2/form' method='POST' style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
    echo "<input type='hidden' name='amount' value='" . $scenario['amount'] . "'>";
    echo "<input type='hidden' name='tax_amount' value='0'>";
    echo "<input type='hidden' name='product_service_charge' value='0'>";
    echo "<input type='hidden' name='product_delivery_charge' value='0'>";
    echo "<input type='hidden' name='total_amount' value='" . $scenario['amount'] . "'>";
    echo "<input type='hidden' name='transaction_uuid' value='" . $transaction_uuid . "'>";
    echo "<input type='hidden' name='product_code' value='EPAYTEST'>";
    echo "<input type='hidden' name='success_url' value='http://localhost/MISCOFFEE/payment_success.php'>";
    echo "<input type='hidden' name='failure_url' value='http://localhost/MISCOFFEE/payment_failed.php'>";
    echo "<input type='hidden' name='signed_field_names' value='total_amount,transaction_uuid,product_code'>";
    echo "<input type='hidden' name='signature' value='" . $signature . "'>";
    echo "<button type='submit' style='background: #60bb46; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;'>Test " . $scenario['currency'] . " " . $scenario['amount'] . "</button>";
    echo "</form>";
    echo "<hr>";
}

// Test with currency parameter
echo "<h2>Test with Currency Parameter</h2>";

$npr_amount = 2200; // Converted from $22
$transaction_uuid = 'TXN_' . time() . '_CURR';

echo "<h3>NPR Amount with Currency Parameter</h3>";
echo "<form action='https://rc-epay.esewa.com.np/api/epay/main/v2/form' method='POST' style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
echo "<input type='hidden' name='amount' value='" . $npr_amount . "'>";
echo "<input type='hidden' name='tax_amount' value='0'>";
echo "<input type='hidden' name='product_service_charge' value='0'>";
echo "<input type='hidden' name='product_delivery_charge' value='0'>";
echo "<input type='hidden' name='total_amount' value='" . $npr_amount . "'>";
echo "<input type='hidden' name='transaction_uuid' value='" . $transaction_uuid . "'>";
echo "<input type='hidden' name='product_code' value='EPAYTEST'>";
echo "<input type='hidden' name='currency' value='NPR'>";
echo "<input type='hidden' name='success_url' value='http://localhost/MISCOFFEE/payment_success.php'>";
echo "<input type='hidden' name='failure_url' value='http://localhost/MISCOFFEE/payment_failed.php'>";
echo "<input type='hidden' name='signed_field_names' value='total_amount,transaction_uuid,product_code'>";
echo "<input type='hidden' name='signature' value='" . base64_encode(hash_hmac('sha256', "total_amount=" . $npr_amount . ",transaction_uuid=" . $transaction_uuid . ",product_code=EPAYTEST", $secret_key, true)) . "'>";
echo "<button type='submit' style='background: #60bb46; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;'>Test NPR with Currency</button>";
echo "</form>";

// Test with old eSewa format
echo "<h2>Test with Old eSewa Format (NPR)</h2>";

echo "<form action='https://esewa.com.np/epay/main' method='POST' style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
echo "<input type='hidden' name='amt' value='" . $npr_amount . "'>";
echo "<input type='hidden' name='pdc' value='0'>";
echo "<input type='hidden' name='psc' value='0'>";
echo "<input type='hidden' name='txAmt' value='0'>";
echo "<input type='hidden' name='tAmt' value='" . $npr_amount . "'>";
echo "<input type='hidden' name='pid' value='" . $transaction_uuid . "'>";
echo "<input type='hidden' name='scd' value='EPAYTEST'>";
echo "<input type='hidden' name='su' value='http://localhost/MISCOFFEE/payment_success.php'>";
echo "<input type='hidden' name='fu' value='http://localhost/MISCOFFEE/payment_failed.php'>";
echo "<button type='submit' style='background: #60bb46; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;'>Test Old Format (NPR)</button>";
echo "</form>";

echo "<h2>Currency Conversion Guide</h2>";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>USD Amount</th><th>NPR Amount (Approx)</th><th>Description</th></tr>";
echo "<tr><td>$1</td><td>100 NPR</td><td>Small amount</td></tr>";
echo "<tr><td>$5</td><td>500 NPR</td><td>Medium amount</td></tr>";
echo "<tr><td>$10</td><td>1000 NPR</td><td>Standard amount</td></tr>";
echo "<tr><td>$22</td><td>2200 NPR</td><td>Your current amount</td></tr>";
echo "<tr><td>$50</td><td>5000 NPR</td><td>Large amount</td></tr>";
echo "<tr><td>$100</td><td>10000 NPR</td><td>Very large amount</td></tr>";
echo "</table>";

echo "<h2>Recommendations</h2>";
echo "<p><strong>1. Use NPR Amounts:</strong> Convert your USD amounts to NPR</p>";
echo "<p><strong>2. Test Scenario 2:</strong> Try the NPR amount (2200 NPR) first</p>";
echo "<p><strong>3. Use Old Format:</strong> The old eSewa format might be more forgiving</p>";
echo "<p><strong>4. Check eSewa Documentation:</strong> Verify the expected currency format</p>";

echo "<h2>Next Steps</h2>";
echo "<p>1. Try the NPR amount tests above</p>";
echo "<p>2. If NPR works, update your payment system to use NPR</p>";
echo "<p>3. If still getting 400 error, the issue is elsewhere</p>";

echo "<p><a href='esewa_payment.php'>Back to Payment Page</a></p>";
?> 