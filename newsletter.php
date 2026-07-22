<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/phpmailer/Exception.php';
require_once __DIR__ . '/phpmailer/PHPMailer.php';
require_once __DIR__ . '/phpmailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$input = json_decode(file_get_contents('php://input'), true);

$email = trim($input['email'] ?? '');

if (!$email) {
    http_response_code(400);
    echo json_encode(['error' => 'Email is required']);
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid email format']);
    exit();
}

try {
    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host       = gethostbyname(SMTP_HOST);
    $isLocalhost = (SMTP_HOST === 'localhost' || SMTP_HOST === '127.0.0.1');

    if ($isLocalhost) {
        $mail->SMTPAuth   = false;
        $mail->SMTPSecure = '';
        $mail->SMTPAutoTLS = false;
    } else {
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        if (defined('SMTP_PORT') && SMTP_PORT == 465) {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        } else {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        }
    }
    $mail->Port       = SMTP_PORT;

    $mail->setFrom(SMTP_USER, 'SYND Ghana');
    $mail->addAddress(NEWSLETTER_EMAIL);

    $mail->isHTML(true);
    $mail->Subject = 'New Newsletter Subscription';
    $mail->Body    = "
        <h2>New Newsletter Subscription</h2>
        <p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>
        <p><strong>Date:</strong> " . date('F j, Y, g:i a T') . "</p>
    ";
    $mail->AltBody = "New newsletter subscription from: $email\nDate: " . date('F j, Y, g:i a T');

    $mail->send();

    echo json_encode(['success' => true]);
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to send email',
        'message' => $e->getMessage(),
        'mailError' => isset($mail) ? $mail->ErrorInfo : ''
    ]);
}
