<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require_once "db.php";
require_once "mailer.php";

$data = json_decode(file_get_contents("php://input"), true);

$email = strtolower(trim($data['email'] ?? ""));

if ($email === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["status"=>"error","message"=>"Invalid email address"]);
    exit;
}

/* CHECK USER */
$stmt = $conn->prepare("SELECT id FROM users WHERE LOWER(email)=? LIMIT 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if (!$result->fetch_assoc()) {
    echo json_encode(["status"=>"error","message"=>"Email not registered"]);
    exit;
}

/* GENERATE OTP */
$otp        = rand(100000, 999999);
$otpExpires = date("Y-m-d H:i:s", strtotime("+5 minutes"));

$update = $conn->prepare(
    "UPDATE users SET otp=?, otp_expires=? WHERE LOWER(email)=?"
);
$update->bind_param("sss", $otp, $otpExpires, $email);
$update->execute();

/* SEND MAIL */
sendOTP($email, $otp);

echo json_encode([
    "status"=>"success",
    "message"=>"Verification code sent to email"
]);
