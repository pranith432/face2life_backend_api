<?php
require "db.php";

if (!isset($_POST['user_id']) || !isset($_FILES['image'])) {
    echo json_encode(["status"=>"error","message"=>"Missing data"]);
    exit;
}

$user_id = intval($_POST['user_id']);

$uploadDir = "uploads/";
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$ext = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
$fileName = "profile_" . $user_id . "_" . time() . "." . $ext;
$filePath = $uploadDir . $fileName;

if (move_uploaded_file($_FILES["image"]["tmp_name"], $filePath)) {

    $stmt = $conn->prepare(
        "UPDATE users SET profile_image=?, updated_at=NOW() WHERE id=?"
    );
    $stmt->bind_param("si", $filePath, $user_id);
    $stmt->execute();

    echo json_encode([
        "status"=>"success",
        "image"=>$filePath
    ]);
} else {
    echo json_encode(["status"=>"error","message"=>"Upload failed"]);
}
