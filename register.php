<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require_once "db.php";
require_once "mailer.php";

/* READ JSON INPUT */
$data = json_decode(file_get_contents("php://input"), true);

$name     = trim($data['full_name'] ?? "");
$email    = strtolower(trim($data['email'] ?? ""));
$password = $data['password'] ?? "";

/* ---------------- VALIDATIONS ---------------- */

// Name
if ($name === "" || !preg_match("/^[A-Za-z ]+$/", $name)) {
    echo json_encode([
        "status" => "error",
        "message" => "Name should contain only letters"
    ]);
    exit;
}

// Email
if ($email === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid email address"
    ]);
    exit;
}

// Password
if (!preg_match("/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@#\$%&!]).{8,}$/", $password)) {
    echo json_encode([
        "status" => "error",
        "message" => "Password does not meet requirements"
    ]);
    exit;
}

/* ---------------- CHECK EMAIL ---------------- */

$check = $conn->prepare(
    "SELECT id, verified FROM users WHERE LOWER(email)=? LIMIT 1"
);
$check->bind_param("s", $email);
$check->execute();
$result = $check->get_result();

if ($row = $result->fetch_assoc()) {

    if ($row['verified'] == 1) {
        // Already verified user
        echo json_encode([
            "status" => "error",
            "message" => "Email already registered"
        ]);
        exit;
    } else {
        // Not verified → delete old record and allow re-register
        $delete = $conn->prepare(
            "DELETE FROM users WHERE id=?"
        );
        $delete->bind_param("i", $row['id']);
        $delete->execute();
    }
}

/* ---------------- REGISTER USER ---------------- */

$otp        = rand(100000, 999999);
$otpExpires = date("Y-m-d H:i:s", strtotime("+5 minutes"));
$hashedPwd  = password_hash($password, PASSWORD_BCRYPT);

$stmt = $conn->prepare(
    "INSERT INTO users (full_name, email, password, otp, otp_expires, verified)
     VALUES (?, ?, ?, ?, ?, 0)"
);
$stmt->bind_param(
    "sssss",
    $name,
    $email,
    $hashedPwd,
    $otp,
    $otpExpires
);

if ($stmt->execute()) {

    sendOTP($email, $otp);

    echo json_encode([
        "status" => "success",
        "message" => "OTP sent to email"
    ]);

} else {
    echo json_encode([
        "status" => "error",
        "message" => "Registration failed"
    ]);
}
