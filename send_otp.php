<?php
require "db.php";
require "mailer.php";

$data = json_decode(file_get_contents("php://input"), true);
$email = trim($data['email'] ?? "");

$stmt = $conn->prepare("SELECT id FROM users WHERE email=?");
$stmt->bind_param("s",$email);
$stmt->execute();
$result = $stmt->get_result();

if (!$result->fetch_assoc()) {
    echo json_encode(["status"=>"error","message"=>"Email not registered"]);
    exit;
}

$otp = rand(100000,999999);
$otp_expires = date("Y-m-d H:i:s", strtotime("+5 minutes"));

$update = $conn->prepare(
    "UPDATE users SET otp=?, otp_expires=? WHERE email=?"
);
$update->bind_param("sss",$otp,$otp_expires,$email);
$update->execute();

sendOTP($email,$otp);

echo json_encode(["status"=>"success","message"=>"OTP sent"]);
