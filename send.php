<?php
/**
 * Email Sending Service
 * 
 * This script handles email sending via Mailgun SMTP
 * with support for Arabic and English responses.
 */

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Set headers for JSON response and CORS
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Max-Age: 86400');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed. Only POST requests are accepted.',
        'messageAr' => 'الطريقة غير مسموح بها. يتم قبول طلبات POST فقط.'
    ]);
    exit();
}

// Load dependencies
require_once __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dotenv\Dotenv;

try {
    // Load environment variables
    $dotenv = Dotenv::createImmutable(__DIR__);
    $dotenv->load();
    
    // Get and decode JSON body
    $jsonBody = file_get_contents('php://input');
    $data = json_decode($jsonBody, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON data');
    }
    
    // Validate required fields
    $requiredFields = ['name', 'email', 'message', 'phone', 'service', 'companyName'];
    $missingFields = [];
    
    foreach ($requiredFields as $field) {
        if (empty($data[$field])) {
            $missingFields[] = $field;
        }
    }
    
    if (!empty($missingFields)) {
        $lang = isset($data['lang']) ? $data['lang'] : 'ar';
        $message = $lang === 'en' 
            ? 'Missing required fields: ' . implode(', ', $missingFields)
            : 'حقول مطلوبة مفقودة: ' . implode(', ', $missingFields);
            
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $message,
            'messageAr' => 'حقول مطلوبة مفقودة: ' . implode(', ', $missingFields),
            'missing_fields' => $missingFields
        ]);
        exit();
    }
    
    // Validate email format
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $lang = isset($data['lang']) ? $data['lang'] : 'ar';
        $message = $lang === 'en' 
            ? 'Invalid email address'
            : 'عنوان البريد الإلكتروني غير صالح';
            
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $message,
            'messageAr' => 'عنوان البريد الإلكتروني غير صالح'
        ]);
        exit();
    }
    
    // Extract data
    $name = htmlspecialchars($data['name'], ENT_QUOTES, 'UTF-8');
    $email = htmlspecialchars($data['email'], ENT_QUOTES, 'UTF-8');
    $phone = htmlspecialchars($data['phone'], ENT_QUOTES, 'UTF-8');
    $service = htmlspecialchars($data['service'], ENT_QUOTES, 'UTF-8');
    $companyName = htmlspecialchars($data['companyName'], ENT_QUOTES, 'UTF-8');
    $message = htmlspecialchars($data['message'], ENT_QUOTES, 'UTF-8');
    $lang = isset($data['lang']) ? $data['lang'] : 'ar';
    
    // Load email template
    $templatePath = __DIR__ . '/email-template.html';
    if (!file_exists($templatePath)) {
        throw new Exception('Email template not found');
    }
    
    $htmlTemplate = file_get_contents($templatePath);
    
    // Replace placeholders in template
    $htmlBody = str_replace(
        ['{{name}}', '{{email}}', '{{phone}}', '{{service}}', '{{companyName}}', '{{message}}'],
        [$name, $email, $phone, $service, $companyName, nl2br($message)],
        $htmlTemplate
    );
    
    // Create PHPMailer instance
    $mail = new PHPMailer(true);
    
    // Server settings
    $mail->isSMTP();
    $mail->Host       = $_ENV['MAILGUN_SMTP_HOST'];
    $mail->SMTPAuth   = true;
    $mail->Username   = $_ENV['MAILGUN_SMTP_USER'];
    $mail->Password   = $_ENV['MAILGUN_SMTP_PASSWORD'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = $_ENV['MAILGUN_SMTP_PORT'];
    $mail->CharSet    = 'UTF-8';
    
    // Set timeout
    $mail->Timeout = 30;
    $mail->SMTPKeepAlive = true;
    
    // Recipients
    $mail->setFrom(
        $_ENV['MAIL_FROM_ADDRESS'], 
        $_ENV['MAIL_FROM_NAME']
    );
    $mail->addAddress(
        $_ENV['MAIL_TO_ADDRESS'], 
        $_ENV['MAIL_TO_NAME']
    );
    $mail->addReplyTo($email, $name);
    
    // Content
    $mail->isHTML(true);
    $mail->Subject = sprintf('رسالة جديدة من %s - %s', $name, $companyName);
    $mail->Body    = $htmlBody;
    $mail->AltBody = sprintf(
        "الاسم: %s\nالبريد الإلكتروني: %s\nالهاتف: %s\nالشركة: %s\nالخدمة: %s\nالرسالة: %s",
        $name, $email, $phone, $companyName, $service, $message
    );
    
    // Send email
    $mail->send();
    
    // Log successful send (optional)
    if ($_ENV['APP_DEBUG'] === 'true') {
        error_log(sprintf(
            '[%s] Email sent successfully from %s (%s)',
            date('Y-m-d H:i:s'),
            $name,
            $email
        ));
    }
    
    // Return success response
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => $lang === 'en' 
            ? 'Email sent successfully! We will contact you soon.'
            : 'تم إرسال البريد الإلكتروني بنجاح! سنتواصل معك قريباً.',
        'messageAr' => 'تم إرسال البريد الإلكتروني بنجاح! سنتواصل معك قريباً.'
    ]);
    
} catch (Exception $e) {
    // Log error
    error_log(sprintf(
        '[%s] Email sending failed: %s',
        date('Y-m-d H:i:s'),
        $e->getMessage()
    ));
    
    // Determine language
    $lang = isset($data['lang']) ? $data['lang'] : 'ar';
    
    // Return error response
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $lang === 'en' 
            ? 'Failed to send email. Please try again later.'
            : 'فشل إرسال البريد الإلكتروني. يرجى المحاولة مرة أخرى لاحقاً.',
        'messageAr' => 'فشل إرسال البريد الإلكتروني. يرجى المحاولة مرة أخرى لاحقاً.',
        'error' => $_ENV['APP_DEBUG'] === 'true' ? $e->getMessage() : 'Internal server error'
    ]);
}
