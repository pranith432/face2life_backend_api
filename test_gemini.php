<?php
header("Content-Type: application/json");

$apiKey = "AIzaSyB07nmIDhyAglk4LEMNCCxwRUF43p6CSIc";

$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=$apiKey";

$payload = [
    "contents" => [[
        "parts" => [
            [
                "text" => "Reply ONLY in JSON like {\"status\":\"ok\",\"msg\":\"Gemini 2.5 Flash working\"}"
            ]
        ]
    ]]
];

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => ["Content-Type: application/json"],
    CURLOPT_POSTFIELDS => json_encode($payload)
]);

$response = curl_exec($ch);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo json_encode(["error" => $error]);
    exit;
}

echo $response;
