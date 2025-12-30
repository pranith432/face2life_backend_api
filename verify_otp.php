<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require_once "db.php";

/* READ JSON INPUT */
$data = json_decode(file_get_contents("php://input"), true);

$email = strtolower(trim($data['email'] ?? ""));
$otp   = trim($data['otp'] ?? "");

/* VALIDATION */
if ($email === "" || $otp === "") {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid request"
    ]);
    exit;
}

/* FETCH USER */
$stmt = $conn->prepare(
    "SELECT otp, otp_expires FROM users WHERE LOWER(email)=? LIMIT 1"
);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

$row = $result->fetch_assoc();

if (!$row) {
    echo json_encode([
        "status" => "error",
        "message" => "User not found"
    ]);
    exit;
}

/* CHECK OTP EXPIRY */
if (date("Y-m-d H:i:s") > $row['otp_expires']) {
    echo json_encode([
        "status" => "error",
        "message" => "OTP expired. Please resend OTP"
    ]);
    exit;
}

/* CHECK OTP */
if ($row['otp'] !== $otp) {
    echo json_encode([
        "status" => "error",
        "message" => "OTP entered is wrong. Please enter correct OTP"
    ]);
    exit;
}

/* MARK USER AS VERIFIED */
$update = $conn->prepare(
    "UPDATE users 
     SET verified = 1, otp = NULL, otp_expires = NULL
     WHERE LOWER(email)=?"
);
$update->bind_param("s", $email);
$update->execute();

echo json_encode([
    "status" => "success",
    "message" => "OTP verified successfully"
]);
