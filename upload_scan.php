<?php
require "config.php";

if (!isset($_FILES["image"])) {
    echo json_encode(["status"=>"error","message"=>"Image not found"]);
    exit;
}

$fileName = time() . "_" . basename($_FILES["image"]["name"]);
$targetPath = UPLOAD_DIR . $fileName;

if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetPath)) {
    echo json_encode([
        "status" => "success",
        "image_path" => $targetPath
    ]);
} else {
    echo json_encode(["status"=>"error","message"=>"Upload failed"]);
}
