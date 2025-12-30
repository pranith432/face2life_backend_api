<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require_once "db.php";

/* READ JSON INPUT */
$data = json_decode(file_get_contents("php://input"), true);

$email    = strtolower(trim($data['email'] ?? ""));
$password = $data['password'] ?? "";

/* VALIDATION */
if ($email === "" || $password === "") {
    echo json_encode([
        "status" => "error",
        "message" => "Email and password are required"
    ]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid email address"
    ]);
    exit;
}

/* FETCH USER */
$stmt = $conn->prepare(
    "SELECT id, full_name, password, verified 
     FROM users 
     WHERE LOWER(email)=? 
     LIMIT 1"
);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

$user = $result->fetch_assoc();

if (!$user) {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid email or password"
    ]);
    exit;
}

/* CHECK VERIFIED */
if ($user['verified'] == 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Please verify your account before login"
    ]);
    exit;
}

/* CHECK PASSWORD */
if (!password_verify($password, $user['password'])) {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid email or password"
    ]);
    exit;
}

/* SUCCESS */
echo json_encode([
    "status" => "success",
    "message" => "Login successful",
    "username" => $user['full_name']
]);
