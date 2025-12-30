<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

/* =======================
   1. Validate image upload
   ======================= */
if (!isset($_FILES["image"]) || $_FILES["image"]["error"] !== UPLOAD_ERR_OK) {
    echo json_encode(["error" => "Image missing or upload failed"]);
    exit;
}

$tmpPath = $_FILES["image"]["tmp_name"];
$mimeType = mime_content_type($tmpPath);
$fileSize = filesize($tmpPath);

if ($fileSize < 1000) {
    echo json_encode(["error" => "Image file too small"]);
    exit;
}

/* =======================
   2. Convert image to Base64
   ======================= */
$imageData = base64_encode(file_get_contents($tmpPath));

/* =======================
   3. Gemini API Config
   ======================= */
$apiKey = "AIzaSyB07nmIDhyAglk4LEMNCCxwRUF43p6CSIc"; // 🔴 put new key here
$model  = "gemini-2.5-flash";

$url = "https://generativelanguage.googleapis.com/v1beta/models/$model:generateContent?key=$apiKey";

/* =======================
   4. Prompt (STRICT JSON)
   ======================= */
$prompt = <<<PROMPT
Analyze the face image and respond ONLY with valid JSON.
Do NOT include markdown, explanation, or extra text.

{
  "pimples": "Detected or Not detected",
  "darkCircles": "Present or Absent",
  "skinTone": "Even or Uneven",
  "remedies": [
    "short remedy 1",
    "short remedy 2",
    "short remedy 3"
  ]
}
PROMPT;

/* =======================
   5. Request Payload
   ======================= */
$payload = [
    "contents" => [[
        "parts" => [
            ["text" => $prompt],
            [
                "inlineData" => [
                    "mimeType" => $mimeType,
                    "data" => $imageData
                ]
            ]
        ]
    ]]
];

/* =======================
   6. CURL Call
   ======================= */
$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => ["Content-Type: application/json"],
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_TIMEOUT => 60
]);

$response = curl_exec($ch);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo json_encode(["error" => $error]);
    exit;
}

/* =======================
   7. Debug Gemini response
   ======================= */
file_put_contents("gemini_debug.txt", $response);

/* =======================
   8. Extract text safely
   ======================= */
$data = json_decode($response, true);

$text = $data["candidates"][0]["content"]["parts"][0]["text"] ?? "";

if (!$text) {
    echo json_encode(["error" => "Empty Gemini response"]);
    exit;
}

/* =======================
   9. Clean & parse JSON
   ======================= */
$text = preg_replace('/```json|```/i', '', $text);
$text = trim($text);

$finalJson = json_decode($text, true);

if (!$finalJson) {
    echo json_encode([
        "error" => "Invalid JSON from Gemini",
        "raw" => $text
    ]);
    exit;
}

/* =======================
   10. Success
   ======================= */
echo json_encode($finalJson);