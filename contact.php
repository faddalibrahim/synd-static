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

$name    = trim($input['name'] ?? '');
$email   = trim($input['email'] ?? '');
$message = trim($input['message'] ?? '');

if (!$name || !$email || !$message) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields']);
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
    $mail->Host       = SMTP_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = SMTP_USER;
    $mail->Password   = SMTP_PASS;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = SMTP_PORT;

    $mail->setFrom(SMTP_USER, 'SYND Ghana');
    $mail->addAddress(CONTACT_EMAIL);
    $mail->addReplyTo($email, $name);

    $mail->isHTML(true);
    $mail->Subject = 'Contact Form: ' . $name;
    $mail->Body    = "
        <h2>New Contact Form Submission</h2>
        <p><strong>Name:</strong> " . htmlspecialchars($name) . "</p>
        <p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>
        <h3>Message:</h3>
        <p>" . nl2br(htmlspecialchars($message)) . "</p>
    ";
    $mail->AltBody = "Name: $name\nEmail: $email\n\nMessage:\n$message";

    $mail->send();

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to send email']);
}
