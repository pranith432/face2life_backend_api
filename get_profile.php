<?php
header("Content-Type: application/json");
require "db.php";

// ✅ Validate input
if (!isset($_GET['user_id'])) {
    echo json_encode([
        "status" => "error",
        "message" => "user_id missing"
    ]);
    exit;
}

$user_id = intval($_GET['user_id']);

$stmt = $conn->prepare(
    "SELECT username, email, phone, bio, profile_image 
     FROM users 
     WHERE id = ?"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {

    echo json_encode(array_merge(
        ["status" => "success"],
        $row
    ));

} else {
    echo json_encode([
        "status" => "error",
        "message" => "User not found"
    ]);
}
