<?php
// Adjust the path to correctly include PHPMailer
require __DIR__ . '/../../phpmailer/src/PHPMailer.php';
require __DIR__ . '/../../phpmailer/src/SMTP.php';
require __DIR__ . '/../../phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
// die(json_encode(["status" => "error", "message" => "Invalid request method"]));
// Error handling
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode(["status" => "error", "message" => "Invalid request method"]));
}

// Capture and sanitize input
$name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
$phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
$company = filter_input(INPUT_POST, 'company', FILTER_SANITIZE_STRING);
$message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);

if (!$name || !$email || !$message) {
    die(json_encode(["status" => "error", "message" => "Invalid input provided"]));
}

$expiryTime = time() + (24 * 60 * 60); // 24 hours from now
$secretKey = "tdB69MgkfnwOCPwOKiMaZqNfi4L0TalN"; // Keep this secure and private

// Encode details into a confirmation URL
$encodedData = base64_encode(json_encode([
    "name" => $name,
    "email" => $email,
    "message" => $message,
    "phone" => $phone,
    "company"=>$company,
    "expires_at" => $expiryTime
]));
$signature = hash_hmac('sha256', $encodedData, $secretKey); // Generate a secure signature

// $confirmUrl = "https://dev2.civentech.com/confirm.php?data=" . rawurlencode($encodedData) . "&signature=" . rawurlencode($signature);
// $confirmUrl = "https://dev.civentech.com.com/confirm.php?data=" . $encodedData;
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$baseUrl = $protocol . "://" . $host;

$confirmUrl = $baseUrl . "/confirm.php?data=" . rawurlencode($encodedData) . "&signature=" . rawurlencode($signature);

// Email configuration

$mail = new PHPMailer(true);
try {
   
    $mail->isSMTP();
    $mail->Host = 'smtp.civentech.com'; // Set your SMTP server
    $mail->SMTPAuth = true;
    $mail->Username = 'contact@civentech.com'; 
    $mail->Password = 's!>MXZ?HXB£v'; 
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom('contact@civentech.com', 'Civentech');
    $mail->addAddress($email, $name);

    $mail->isHTML(true);
    $mail->Subject = "Confirm Your Contact Request - Civentech";
    $mail->Body = "
    <div style='font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px; text-align: center;'>
        <div style='max-width: 600px; background: #ffffff; padding: 20px; border-radius: 8px; margin: auto;'>
            <img src='https://dev2.civentech.com/assets/logo_blue.png' alt='Civentech Logo' style='max-width: 150px; margin-bottom: 20px;'>
            <h2 style='color: #2563eb;'>Thank You for Reaching Out to Civentech</h2>
            <p style='color: #333; font-size: 16px;'>Dear <strong>{$name}</strong>,</p>
            <p style='color: #555; font-size: 14px;'>
                We have received your inquiry and appreciate you contacting Civentech. Before we proceed, we need to verify your email address.
            </p>
            <p style='color: #555; font-size: 14px;'>
                Please click the button below to confirm your email:
            </p>
            <a href='{$confirmUrl}' 
               style='background: #2563eb; color: #fff; text-decoration: none; padding: 12px 20px; border-radius: 5px; display: inline-block; font-size: 16px;'>
                Verify Email
            </a>
            <p style='color: #d9534f; font-size: 14px; margin-top: 20px;'>
                ⚠️ This link will expire in <strong>24 hours</strong>. If you do not verify your email within this time, you will need to submit the form again.
            </p>
            <p style='color: #777; font-size: 12px; margin-top: 20px;'>
                If you did not request this verification, please ignore this email.
            </p>
            <hr style='border: 0.5px solid #ddd; margin: 20px 0;'>
            <p style='color: #888; font-size: 12px;'>Civentech : Imagine. Build. Scale.</p>
        </div>
    </div>
";


    $mail->send();
    echo json_encode(["status" => "success", "message" => "Confirmation email sent!"]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Email could not be sent. Error: " . $mail->ErrorInfo]);
}
?>
