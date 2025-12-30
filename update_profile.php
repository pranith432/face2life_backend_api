<?php
require "db.php";

$user_id = intval($_POST['user_id']);
$username = trim($_POST['username']);
$phone = trim($_POST['phone']);
$bio = trim($_POST['bio']);

$stmt = $conn->prepare(
  "UPDATE users SET username=?, phone=?, bio=?, updated_at=NOW() WHERE id=?"
);
$stmt->bind_param("sssi", $username, $phone, $bio, $user_id);

if ($stmt->execute()) {
    echo json_encode(["status"=>"success"]);
} else {
    echo json_encode(["status"=>"error"]);
}
