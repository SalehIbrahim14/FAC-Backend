#!/bin/bash

# Test Email Service with cURL
# This script sends test requests to the email service endpoint

echo "=== Email Service cURL Test Script ==="
echo ""

# Configuration
ENDPOINT="http://localhost/send.php"  # Update with your actual URL

# Test 1: Valid Arabic Request
echo "Test 1: Valid Arabic Request"
echo "--------------------------------"
curl -X POST "$ENDPOINT" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "أحمد محمد",
    "email": "ahmed.test@example.com",
    "phone": "+966501234567",
    "companyName": "شركة التقنية المتقدمة",
    "service": "تطوير المواقع",
    "message": "أرغب في الاستفسار عن خدمات تطوير المواقع",
    "lang": "ar"
  }'
echo -e "\n\n"

# Test 2: Valid English Request
echo "Test 2: Valid English Request"
echo "--------------------------------"
curl -X POST "$ENDPOINT" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john.doe@example.com",
    "phone": "+1234567890",
    "companyName": "Tech Corporation",
    "service": "Web Development",
    "message": "I would like to inquire about your web development services",
    "lang": "en"
  }'
echo -e "\n\n"

# Test 3: Missing Required Field
echo "Test 3: Missing Required Field (should return 400)"
echo "----------------------------------------------------"
curl -X POST "$ENDPOINT" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "phone": "+1234567890",
    "companyName": "Test Company",
    "service": "Testing",
    "message": "Test message"
  }'
echo -e "\n\n"

# Test 4: Invalid Email Format
echo "Test 4: Invalid Email Format (should return 400)"
echo "--------------------------------------------------"
curl -X POST "$ENDPOINT" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "email": "invalid-email",
    "phone": "+1234567890",
    "companyName": "Test Company",
    "service": "Testing",
    "message": "Test message"
  }'
echo -e "\n\n"

# Test 5: OPTIONS request (CORS preflight)
echo "Test 5: OPTIONS Request (CORS preflight)"
echo "------------------------------------------"
curl -X OPTIONS "$ENDPOINT" \
  -H "Origin: http://example.com" \
  -H "Access-Control-Request-Method: POST" \
  -H "Access-Control-Request-Headers: Content-Type" \
  -v
echo -e "\n\n"

echo "=== Tests Complete ==="
