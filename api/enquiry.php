<?php
// api/enquiry.php

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    exit("Method Not Allowed");
}

// =========================
// TELEGRAM CONFIG
// =========================
// Replace these with your real values
$botToken = "8630250610:AAHzw-NAJVSuzHHdStgecC2jDwLNXRmu-BM";
$chatId   = "1742601911";

// =========================
// HELPERS
// =========================
function clean($value) {
    return trim((string)($value ?? ""));
}

function escapeTelegramHtml($value) {
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// =========================
// BASIC SPAM CHECK
// =========================
if (!empty($_POST["website"] ?? "")) {
    header("Location: /thank-you.html");
    exit;
}

// =========================
// FORM DATA
// =========================
$name     = clean($_POST["name"] ?? "");
$contact  = clean($_POST["contact"] ?? "");
$service  = clean($_POST["service"] ?? "");
$priority = clean($_POST["priority"] ?? "");
$message  = clean($_POST["message"] ?? "");

// =========================
// VALIDATION
// =========================
if ($name === "" || $contact === "" || $message === "") {
    http_response_code(400);
    exit("Missing required fields.");
}

if (strlen($name) > 120 || strlen($contact) > 200 || strlen($service) > 120 || strlen($priority) > 120 || strlen($message) > 4000) {
    http_response_code(400);
    exit("Input too long.");
}

// =========================
// SAFE OUTPUT
// =========================
$nameSafe     = escapeTelegramHtml($name);
$contactSafe  = escapeTelegramHtml($contact);
$serviceSafe  = escapeTelegramHtml($service);
$prioritySafe = escapeTelegramHtml($priority);
$messageSafe  = escapeTelegramHtml($message);

// =========================
// TELEGRAM MESSAGE
// =========================
$text =
"📩 <b>New Ric Lee DC Enquiry</b>\n\n" .
"<b>Name:</b> {$nameSafe}\n" .
"<b>Contact:</b> {$contactSafe}\n" .
"<b>Service:</b> {$serviceSafe}\n" .
"<b>Priority:</b> {$prioritySafe}\n\n" .
"<b>Project details:</b>\n{$messageSafe}";

// =========================
// SEND TO TELEGRAM
// =========================
$url = "https://api.telegram.org/bot{$botToken}/sendMessage";

$postFields = [
    "chat_id" => $chatId,
    "text" => $text,
    "parse_mode" => "HTML",
    "disable_web_page_preview" => true
];

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $url,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $postFields,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 15,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

// =========================
// RESULT
// =========================
if ($response === false || $httpCode !== 200) {
    http_response_code(500);
    echo "Telegram send failed.";
    if (!empty($curlError)) {
        echo " " . htmlspecialchars($curlError, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
    exit;
}

header("Location: /thank-you.html");
exit;
?>