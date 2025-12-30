<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

define("DB_HOST","localhost");
define("DB_USER","root");
define("DB_PASS","");
define("DB_NAME","face2life_db");

/* EMAIL CONFIG */
define("MAIL_HOST","smtp.gmail.com");
define("MAIL_USER","facetolifeoffice@gmail.com");
define("MAIL_PASS","yanf zbrs oczx lymt"); // 🔴 change
define("MAIL_PORT",587);

/* GEMINI */
define("GEMINI_API_KEY", "AIzaSyBO7mIDhyAgIk4LEMNCcxwRUF43p6CSIc"); // 🔴 change

/* UPLOAD */
define("UPLOAD_DIR", "uploads/");

if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0777, true);
}
