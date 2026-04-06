<?php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /#enquiry');
    exit;
}

$botToken = "8630250610:AAHzw-NAJVSuzHHdStgecC2jDwLNXRmu-BM";
$chatId   = "1742601911";

function clean_input($value) {
    return trim((string)$value);
}

$name     = clean_input($_POST['name'] ?? '');
$contact  = clean_input($_POST['contact'] ?? '');
$service  = clean_input($_POST['service'] ?? '');
$priority = clean_input($_POST['priority'] ?? '');
$message  = clean_input($_POST['message'] ?? '');
$website  = clean_input($_POST['website'] ?? '');

// honeypot
if ($website !== '') {
    header('Location: /?enquiry=spam#enquiry');
    exit;
}

if ($name === '' || $contact === '' || $service === '' || $priority === '' || $message === '') {
    header('Location: /?enquiry=missing#enquiry');
    exit;
}

$text  = "📩 New Ric Lee enquiry\n\n";
$text .= "👤 Name: {$name}\n";
$text .= "📞 Contact: {$contact}\n";
$text .= "🛠 Service: {$service}\n";
$text .= "⚡ Priority: {$priority}\n";
$text .= "💬 Details:\n{$message}";

$url = "https://api.telegram.org/bot{$botToken}/sendMessage";

$postFields = [
    'chat_id' => $chatId,
    'text'    => $text,
];

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $postFields,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 15,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($response !== false && $httpCode === 200) {
    header('Location: /?enquiry=success#enquiry');
    exit;
}

header('Location: /?enquiry=error#enquiry');
exit;
