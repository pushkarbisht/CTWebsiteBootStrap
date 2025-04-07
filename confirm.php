<?php
// Include PHPMailer
require __DIR__ . '/../../phpmailer/src/PHPMailer.php';
require __DIR__ . '/../../phpmailer/src/SMTP.php';
require __DIR__ . '/../../phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$secretKey = "tdB69MgkfnwOCPwOKiMaZqNfi4L0TalN"; // Keep this secure and private // Must match process.php

// Step 1: Validate URL parameters
if (!isset($_GET['data']) || !isset($_GET['signature'])) {
    echo renderResponse("Invalid Request", "The link is invalid or missing. Please try again.", false);
    exit;
}

$encodedData = $_GET['data'];
$providedSignature = $_GET['signature'];

// Step 2: Verify the signature
$calculatedSignature = hash_hmac('sha256', $encodedData, $secretKey);
if (!hash_equals($calculatedSignature, $providedSignature)) {
    echo renderResponse("Security Warning", "The link is invalid or has been tampered with.", false);
    exit;
}

// Step 3: Decode the data
$decodedData = json_decode(base64_decode($encodedData), true);

// Step 4: Validate decoded data
if (!isset($decodedData['name']) || !isset($decodedData['email']) || !isset($decodedData['message']) || !isset($decodedData['expires_at']) || !isset($decodedData['phone'])) {
    echo renderResponse("Invalid Request", "The link is incomplete or tampered with. Please try again.", false);
    exit;
}

$name = htmlspecialchars($decodedData['name']);
$email = htmlspecialchars($decodedData['email']);
$phone = htmlspecialchars($decodedData['phone']);
$company = htmlspecialchars($decodedData['company']);
$message = nl2br(htmlspecialchars($decodedData['message']));
$expiresAt = $decodedData['expires_at'];

// Step 5: Check expiration
if (time() > $expiresAt) {
    echo renderResponse("Link Expired", "Your verification link has expired. Please submit the form again.", false);
    exit;
}

// Admin emails
$adminEmail1 = "er.pushkarbisht@gmail.com";
$adminEmail2 = "abhinav25.bansal@gmail.com";

// Step 6: Setup PHPMailer
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = 'smtp.civentech.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'contact@civentech.com';
    $mail->Password = 's!>MXZ?HXB£v';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    // Send email to Admins
    $mail->setFrom('contact@civentech.com', 'Civentech');
    $mail->addAddress($adminEmail1);
    $mail->addAddress($adminEmail2);
    
    $mail->Subject = "New Lead from {$name} - Civentech";
    $mail->isHTML(true);
    $mail->Body = "
        <div style='font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px; text-align: center;'>
            <div style='max-width: 600px; background: #ffffff; padding: 20px; border-radius: 8px; margin: auto;'>
                <h2 style='color: #2563eb;'>New Contact Form Submission</h2>
                <p><strong>Name:</strong> {$name}</p>
                <p><strong>Email:</strong> {$email}</p>
                <p><strong>Phone:</strong> {$phone}</p>
                <p><strong>Phone:</strong> {$company}</p>
                <p><strong>Message:</strong></p>
                <p style='background: #f9f9f9; padding: 15px; border-radius: 5px; font-size: 14px;'>{$message}</p>
                <hr style='border: 0.5px solid #ddd; margin: 20px 0;'>
                <p style='color: #888; font-size: 12px;'>Civentech; Your Vision, Our Code.</p>
            </div>
        </div>
    ";
    $mail->send();

    // Send confirmation email to Customer
    $mail->clearAddresses();
    $mail->addAddress($email);
    $mail->Subject = "Email Verification Completed - Civentech";
    $mail->Body = "
        <div style='font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px; text-align: center;'>
            <div style='max-width: 600px; background: #ffffff; padding: 20px; border-radius: 8px; margin: auto;'>
                <img src='https://dev2.civentech.com/assets/logo_blue.png' alt='Civentech Logo' style='max-width: 150px; margin-bottom: 20px;'>
                <h2 style='color: #2563eb;'>Email Verification Completed</h2>
                <p style='color: #333; font-size: 16px;'>Dear <strong>{$name}</strong>,</p>
                <p style='color: #555; font-size: 14px;'>Thank you for verifying your email. Our team has received your inquiry, and we will get back to you soon.</p>
                <p style='color: #777; font-size: 12px; margin-top: 20px;'>If you have any further queries, feel free to reply to this email.</p>
                <hr style='border: 0.5px solid #ddd; margin: 20px 0;'>
                <p style='color: #888; font-size: 12px;'>Civentech; Your Vision, Our Code.</p>
            </div>
        </div>
    ";
    $mail->send();

    echo renderResponse("Verification Successful", "Your email has been successfully verified. We will contact you soon.", true);
} catch (Exception $e) {
    echo renderResponse("Error", "There was an issue processing your request. Please try again later.", false);
}

// Function to render an HTML response page
function renderResponse($title, $message, $success) {
    $color = $success ? "#2563eb" : "#d9534f";
    return "
    <html>
    <head>
        <title>{$title} - Civentech</title>
        <style>
            body { font-family: Arial, sans-serif; background-color: #f4f4f4; text-align: center; padding: 50px; }
            .container { max-width: 500px; background: #ffffff; padding: 20px; border-radius: 8px; margin: auto; }
            h2 { color: {$color}; }
            p { color: #555; font-size: 16px; }
            .btn { background: {$color}; color: #fff; text-decoration: none; padding: 10px 20px; border-radius: 5px; display: inline-block; margin-top: 20px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <img src='https://dev2.civentech.com/assets/logo_blue.png' alt='Civentech Logo' style='max-width: 120px; margin-bottom: 20px;'>
            <h2>{$title}</h2>
            <p>{$message}</p>
            <a href='https://dev2.civentech.com/#contact' class='btn'>Go Back to Contact Page</a>
        </div>
    </body>
    </html>";
}
?>
