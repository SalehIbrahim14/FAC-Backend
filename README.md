# FAC-Backend Email Service

PHP-based email service for First Answer Company that sends emails via Mailgun SMTP with support for Arabic and English.

## Features

- ✉️ Send emails via Mailgun SMTP
- 🌍 Multi-language support (Arabic & English)
- 🎨 Beautiful HTML email template with RTL support
- 🔒 Secure environment variable configuration
- 🛡️ Input validation and sanitization
- 🚀 CORS-enabled API endpoint
- 📝 Comprehensive error handling and logging

## Requirements

- PHP 7.4 or higher
- Composer
- Mailgun account with SMTP credentials
- Web server (Apache/Nginx)

## Installation

### 1. Clone the repository

```bash
git clone https://github.com/SalehIbrahim14/FAC-Backend.git
cd FAC-Backend
```

### 2. Install dependencies

```bash
composer install
```

### 3. Configure environment variables

Copy the `.env` file and update with your credentials:

```bash
cp .env .env.local
```

Edit `.env` file with your Mailgun SMTP credentials:

```env
MAILGUN_SMTP_HOST=smtp.mailgun.org
MAILGUN_SMTP_PORT=587
MAILGUN_SMTP_USER=your-mailgun-user@yourdomain.com
MAILGUN_SMTP_PASSWORD=your-mailgun-password
MAIL_FROM_ADDRESS=your-mailgun-user@yourdomain.com
MAIL_FROM_NAME="Your Company Name"
MAIL_TO_ADDRESS=recipient@yourdomain.com
MAIL_TO_NAME="Recipient Name"
APP_DEBUG=false
```

### 4. Set up web server

#### Apache

If using Apache, ensure `mod_rewrite` is enabled and the `.htaccess` file is in place.

#### Nginx

Add this configuration to your Nginx server block:

```nginx
location ~ \.php$ {
    fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
    fastcgi_index send.php;
    include fastcgi_params;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
}

location /send {
    try_files $uri $uri/ /send.php?$query_string;
}
```

### 5. Set permissions

```bash
chmod 755 send.php
chmod 600 .env
```

## Usage

### API Endpoint

**URL:** `https://yourdomain.com/send.php`

**Method:** `POST`

**Content-Type:** `application/json`

### Request Body

```json
{
  "name": "أحمد محمد",
  "email": "ahmed@example.com",
  "phone": "+966501234567",
  "companyName": "شركة التقنية المتقدمة",
  "service": "تطوير المواقع",
  "message": "أرغب في الاستفسار عن خدمات تطوير المواقع",
  "lang": "ar"
}
```

### Request Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| name | string | Yes | Sender's full name |
| email | string | Yes | Sender's email address (must be valid) |
| phone | string | Yes | Sender's phone number |
| companyName | string | Yes | Sender's company name |
| service | string | Yes | Requested service |
| message | string | Yes | Message content |
| lang | string | No | Response language: 'ar' (default) or 'en' |

### Success Response

**Status Code:** `200 OK`

```json
{
  "success": true,
  "message": "تم إرسال البريد الإلكتروني بنجاح! سنتواصل معك قريباً.",
  "messageAr": "تم إرسال البريد الإلكتروني بنجاح! سنتواصل معك قريباً."
}
```

### Error Responses

**Status Code:** `400 Bad Request` (Validation error)

```json
{
  "success": false,
  "message": "حقول مطلوبة مفقودة: email",
  "messageAr": "حقول مطلوبة مفقودة: email",
  "missing_fields": ["email"]
}
```

**Status Code:** `500 Internal Server Error` (Server error)

```json
{
  "success": false,
  "message": "فشل إرسال البريد الإلكتروني. يرجى المحاولة مرة أخرى لاحقاً.",
  "messageAr": "فشل إرسال البريد الإلكتروني. يرجى المحاولة مرة أخرى لاحقاً.",
  "error": "Internal server error"
}
```

## Testing

### Using cURL

#### Arabic Request
```bash
curl -X POST https://yourdomain.com/send.php \
  -H "Content-Type: application/json" \
  -d '{
    "name": "أحمد محمد",
    "email": "ahmed@example.com",
    "phone": "+966501234567",
    "companyName": "شركة التقنية",
    "service": "تطوير المواقع",
    "message": "أرغب في الاستفسار عن خدماتكم",
    "lang": "ar"
  }'
```

#### English Request
```bash
curl -X POST https://yourdomain.com/send.php \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "+1234567890",
    "companyName": "Tech Corp",
    "service": "Web Development",
    "message": "I would like to inquire about your services",
    "lang": "en"
  }'
```

### Using JavaScript (Frontend)

```javascript
async function sendEmail(formData) {
  try {
    const response = await fetch('https://yourdomain.com/send.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(formData)
    });
    
    const result = await response.json();
    
    if (result.success) {
      console.log('Success:', result.message);
    } else {
      console.error('Error:', result.message);
    }
    
    return result;
  } catch (error) {
    console.error('Network error:', error);
    throw error;
  }
}

// Example usage
sendEmail({
  name: 'أحمد محمد',
  email: 'ahmed@example.com',
  phone: '+966501234567',
  companyName: 'شركة التقنية',
  service: 'تطوير المواقع',
  message: 'أرغب في الاستفسار عن خدماتكم',
  lang: 'ar'
});
```

## Security Considerations

1. **Environment Variables**: Never commit `.env` file to version control
2. **Input Validation**: All inputs are validated and sanitized
3. **CORS**: Configure `Access-Control-Allow-Origin` for production
4. **Rate Limiting**: Consider implementing rate limiting to prevent abuse
5. **HTTPS**: Always use HTTPS in production
6. **File Permissions**: Set appropriate file permissions (`.env` should be 600)

## Email Template

The HTML email template (`email-template.html`) features:
- RTL (Right-to-Left) support for Arabic
- Responsive design
- Beautiful gradient header
- Organized information display
- Mobile-friendly layout

### Customization

To customize the email template, edit `email-template.html`. The following placeholders are available:

- `{{name}}` - Sender's name
- `{{email}}` - Sender's email
- `{{phone}}` - Sender's phone
- `{{companyName}}` - Company name
- `{{service}}` - Requested service
- `{{message}}` - Message content

## Troubleshooting

### Email not sending

1. Verify Mailgun SMTP credentials in `.env`
2. Check that port 587 is not blocked by firewall
3. Enable debug mode: `APP_DEBUG=true` in `.env`
4. Check PHP error logs

### CORS errors

1. Verify `Access-Control-Allow-Origin` header is set correctly
2. For specific domains, update the CORS header in `send.php`

### Dependencies not loading

1. Run `composer install` again
2. Verify `vendor/autoload.php` exists
3. Check PHP version compatibility

## Directory Structure

```
FAC-Backend/
├── .env                    # Environment configuration (not in git)
├── .gitignore             # Git ignore rules
├── composer.json          # PHP dependencies
├── send.php               # Main API endpoint
├── email-template.html    # Email HTML template
├── README.md              # This file
└── vendor/                # Composer dependencies (auto-generated)
```

## Support

For issues or questions, please contact:
- Email: saleh.ibrahem.w@gmail.com
- GitHub: [SalehIbrahim14](https://github.com/SalehIbrahim14)

## License

This project is private and proprietary to First Answer Company.

## Changelog

### Version 1.0.0 (2024-12-25)
- Initial PHP implementation
- Mailgun SMTP integration
- Multi-language support (Arabic/English)
- HTML email template with RTL support
- CORS-enabled API endpoint
- Input validation and sanitization
- Comprehensive error handling
