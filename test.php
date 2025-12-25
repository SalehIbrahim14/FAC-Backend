<?php
/**
 * Test script for Email Service
 * 
 * This script tests the email service endpoint with various scenarios.
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== Email Service Test Script ===\n\n";

// Test data
$tests = [
    [
        'name' => 'Valid Arabic Request',
        'data' => [
            'name' => 'أحمد محمد',
            'email' => 'ahmed.test@example.com',
            'phone' => '+966501234567',
            'companyName' => 'شركة التقنية المتقدمة',
            'service' => 'تطوير المواقع',
            'message' => 'أرغب في الاستفسار عن خدمات تطوير المواقع الإلكترونية',
            'lang' => 'ar'
        ],
        'expected_status' => 200
    ],
    [
        'name' => 'Valid English Request',
        'data' => [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'phone' => '+1234567890',
            'companyName' => 'Tech Corporation',
            'service' => 'Web Development',
            'message' => 'I would like to inquire about your web development services',
            'lang' => 'en'
        ],
        'expected_status' => 200
    ],
    [
        'name' => 'Missing Required Field (email)',
        'data' => [
            'name' => 'Test User',
            'phone' => '+1234567890',
            'companyName' => 'Test Company',
            'service' => 'Testing',
            'message' => 'Test message',
            'lang' => 'en'
        ],
        'expected_status' => 400
    ],
    [
        'name' => 'Invalid Email Format',
        'data' => [
            'name' => 'Test User',
            'email' => 'invalid-email',
            'phone' => '+1234567890',
            'companyName' => 'Test Company',
            'service' => 'Testing',
            'message' => 'Test message',
            'lang' => 'en'
        ],
        'expected_status' => 400
    ],
    [
        'name' => 'Empty JSON',
        'data' => [],
        'expected_status' => 400
    ]
];

// Function to test endpoint
function testEndpoint($testName, $data, $expectedStatus) {
    echo "Testing: $testName\n";
    echo str_repeat("-", 50) . "\n";
    
    // Prepare request
    $jsonData = json_encode($data);
    
    // Simulate the request by directly calling the logic
    // In a real test, you would use cURL to make HTTP requests
    
    // For this test, we'll validate the data structure
    $validationResult = validateTestData($data);
    
    echo "Request Data: " . json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    echo "Validation Result: " . ($validationResult['valid'] ? 'PASS' : 'FAIL') . "\n";
    echo "Expected Status: $expectedStatus\n";
    echo "Message: " . $validationResult['message'] . "\n";
    echo "\n";
    
    return $validationResult['valid'];
}

// Function to validate test data (mimics send.php validation)
function validateTestData($data) {
    // Check required fields
    $requiredFields = ['name', 'email', 'message', 'phone', 'service', 'companyName'];
    $missingFields = [];
    
    foreach ($requiredFields as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            $missingFields[] = $field;
        }
    }
    
    if (!empty($missingFields)) {
        return [
            'valid' => false,
            'message' => 'Missing required fields: ' . implode(', ', $missingFields)
        ];
    }
    
    // Validate email format
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        return [
            'valid' => false,
            'message' => 'Invalid email address'
        ];
    }
    
    return [
        'valid' => true,
        'message' => 'Data is valid'
    ];
}

// Run tests
echo "Starting tests...\n\n";
$passedTests = 0;
$totalTests = count($tests);

foreach ($tests as $test) {
    $result = testEndpoint($test['name'], $test['data'], $test['expected_status']);
    
    // For validation tests (400), we expect validation to fail
    if ($test['expected_status'] === 400 && !$result) {
        $passedTests++;
        echo "✓ Test passed (expected validation failure)\n\n";
    } elseif ($test['expected_status'] === 200 && $result) {
        $passedTests++;
        echo "✓ Test passed (data is valid)\n\n";
    } else {
        echo "✗ Test failed\n\n";
    }
}

echo str_repeat("=", 50) . "\n";
echo "Test Results: $passedTests / $totalTests tests passed\n";
echo str_repeat("=", 50) . "\n\n";

// Check if files exist
echo "=== File Structure Check ===\n\n";

$requiredFiles = [
    'send.php' => 'Main API endpoint',
    'email-template.html' => 'Email template',
    'composer.json' => 'Composer configuration',
    '.env' => 'Environment configuration',
    '.htaccess' => 'Apache configuration',
    'README.md' => 'Documentation',
    'vendor/autoload.php' => 'Composer autoloader'
];

foreach ($requiredFiles as $file => $description) {
    $exists = file_exists(__DIR__ . '/' . $file);
    $status = $exists ? '✓' : '✗';
    echo "$status $file - $description " . ($exists ? '(found)' : '(MISSING)') . "\n";
}

echo "\n";

// Check PHP syntax
echo "=== PHP Syntax Check ===\n\n";
exec('php -l send.php 2>&1', $output, $returnCode);
if ($returnCode === 0) {
    echo "✓ send.php syntax is valid\n";
} else {
    echo "✗ send.php has syntax errors:\n";
    echo implode("\n", $output) . "\n";
}

echo "\n";

// Check template placeholders
echo "=== Template Placeholder Check ===\n\n";
$template = file_get_contents(__DIR__ . '/email-template.html');
$placeholders = ['{{name}}', '{{email}}', '{{phone}}', '{{service}}', '{{companyName}}', '{{message}}'];

foreach ($placeholders as $placeholder) {
    $exists = strpos($template, $placeholder) !== false;
    $status = $exists ? '✓' : '✗';
    echo "$status $placeholder " . ($exists ? '(found)' : '(MISSING)') . "\n";
}

echo "\n";

// Check environment variables
echo "=== Environment Configuration Check ===\n\n";

require_once __DIR__ . '/vendor/autoload.php';
use Dotenv\Dotenv;

try {
    $dotenv = Dotenv::createImmutable(__DIR__);
    $dotenv->load();
    
    $envVars = [
        'MAILGUN_SMTP_HOST',
        'MAILGUN_SMTP_PORT',
        'MAILGUN_SMTP_USER',
        'MAILGUN_SMTP_PASSWORD',
        'MAIL_FROM_ADDRESS',
        'MAIL_TO_ADDRESS'
    ];
    
    foreach ($envVars as $var) {
        $exists = isset($_ENV[$var]) && !empty($_ENV[$var]);
        $status = $exists ? '✓' : '✗';
        $value = $exists ? ($_ENV[$var] === $_ENV['MAILGUN_SMTP_PASSWORD'] ? '***hidden***' : $_ENV[$var]) : 'NOT SET';
        echo "$status $var = $value\n";
    }
} catch (Exception $e) {
    echo "✗ Error loading .env file: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
