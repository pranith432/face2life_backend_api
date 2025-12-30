<?php
require "db.php";
require "mailer.php";

$data = json_decode(file_get_contents("php://input"), true);
$email = $data['email'] ?? "";

$otp = rand(100000,999999);
$otp_expires = date("Y-m-d H:i:s", strtotime("+5 minutes"));

$stmt = $conn->prepare(
    "UPDATE users SET otp=?, otp_expires=? WHERE email=?"
);
$stmt->bind_param("sss",$otp,$otp_expires,$email);
$stmt->execute();

sendOTP($email,$otp);

echo json_encode(["status"=>"success","message"=>"OTP resent"]);
