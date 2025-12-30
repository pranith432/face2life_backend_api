<?php
require "db.php";
$email = $_GET['email'];

$res = $conn->query("SELECT id FROM users WHERE email='$email'");
echo json_encode(["exists"=>$res->num_rows > 0]);
