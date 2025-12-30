<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require_once "db.php";

$data = json_decode(file_get_contents("php://input"), true);

$email       = strtolower(trim($data['email'] ?? ""));
$newPassword = $data['new_password'] ?? "";

if ($email === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["status"=>"error","message"=>"Invalid email"]);
    exit;
}

if (!preg_match("/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@#\$%&!]).{8,}$/", $newPassword)) {
    echo json_encode(["status"=>"error","message"=>"Password does not meet requirements"]);
    exit;
}

$hashedPwd = password_hash($newPassword, PASSWORD_BCRYPT);

$stmt = $conn->prepare(
    "UPDATE users 
     SET password=?, otp=NULL, otp_expires=NULL 
     WHERE LOWER(email)=?"
);
$stmt->bind_param("ss", $hashedPwd, $email);

if ($stmt->execute()) {
    echo json_encode(["status"=>"success","message"=>"Password reset successfully"]);
} else {
    echo json_encode(["status"=>"error","message"=>"Password reset failed"]);
}
