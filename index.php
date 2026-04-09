<?php

header('Content-Type: application/json');
require_once __DIR__ . '/config.php';

// ====== ENABLE OR DISABLE DEBUG LOGGING HERE ======
$DEBUG_LOG = true; // Set to false to disable debug.log file logging
// ================================================

// Helper function for debug logging
function debug_log($message) {
    global $DEBUG_LOG;
    if ($DEBUG_LOG) {
        file_put_contents(__DIR__ . '/debug.log', date('c') . " $message\n", FILE_APPEND);
    }
}

debug_log("script started");

// 1. Receive webhook data
$webhookData = file_get_contents("php://input");
debug_log("RAW input: $webhookData");

$data = json_decode($webhookData, true);
if (!$data) {
    debug_log("JSON decode error");
    exit("❌ JSON decode error.");
}

// Check which type of webhook this is
$event = $data['data']['event'] ?? '';
debug_log("Webhook event type: $event");

// Only process message webhooks
if ($event !== 'received_message' && $event !== 'messages.upsert') {
    debug_log("Ignoring non-message event: $event");
    exit("⚠️ Ignoring non-message event.");
}

// 2. Robustly extract WhatsApp number and message
// Handle both webhook formats
if ($event === 'received_message') {
    // Format 3: received_message event
    $bodyMsg = $data['data']['message']['body_message'] ?? [];
    $messageKey = $data['data']['message']['message_key'] ?? [];
} else {
    // Format 2: messages.upsert event
    $messages = $data['data']['data']['messages'] ?? [];
    if (empty($messages)) {
        debug_log("No messages in webhook");
        exit("❌ No messages found in webhook.");
    }
    $firstMessage = $messages[0];
    $messageKey = $firstMessage['key'] ?? [];
    
    // Extract message text from extendedTextMessage
    $bodyMsg = [
        'messages' => $firstMessage['message'] ?? [],
        'content' => $firstMessage['message']['extendedTextMessage']['text'] ?? ''
    ];
}

$remoteJid = $messageKey['remoteJid'] ?? '';
$remoteJidAlt = $messageKey['remoteJidAlt'] ?? '';

debug_log("remoteJid: $remoteJid, remoteJidAlt: $remoteJidAlt");

// IGNORE LOGIC FOR GROUPS, BROADCASTS, NEWSLETTERS, ETC.
$ignoreJidPatterns = [
    '@g.us',              // WhatsApp groups
    '@broadcast',         // Broadcast lists
    '@newsletter',        // Newsletter
    'status@broadcast',   // Status updates
    '-@g.us',             // Groups with dash
    'broadcast',          // Just broadcast
    'newsletter',         // Just newsletter
    'status',             // Status messages all
];

foreach ($ignoreJidPatterns as $pattern) {
    if (stripos($remoteJid, $pattern) !== false) {
        debug_log("Ignored remoteJid (matched pattern '$pattern'): $remoteJid");
        exit("❌ Ignored remoteJid: $remoteJid");
    }
}

// Try all possible places for text:
$messageText = '';
if (!empty($bodyMsg['messages']['conversation'])) {
    $messageText = $bodyMsg['messages']['conversation'];
} elseif (!empty($bodyMsg['content'])) {
    $messageText = $bodyMsg['content'];
} elseif (!empty($bodyMsg['messages']['extendedTextMessage']['text'])) {
    $messageText = $bodyMsg['messages']['extendedTextMessage']['text'];
}

debug_log("messageText: $messageText, remoteJid: $remoteJid");

if (!$messageText || !$remoteJid) {
    debug_log("Missing messageText or remoteJid");
    exit("❌ Invalid webhook format.");
}

// Extract phone number - PREFER remoteJidAlt format
$number = '';

// First try remoteJidAlt (this has the correct WhatsApp format)
if (!empty($remoteJidAlt) && preg_match('/^(\d+)@/', $remoteJidAlt, $matchesNumber)) {
    $number = $matchesNumber[1];
    debug_log("Using phone number from remoteJidAlt: $number");
} 
// Fallback to remoteJid
elseif (preg_match('/^(\d+)@/', $remoteJid, $matchesNumber)) {
    $number = $matchesNumber[1];
    debug_log("Using phone number from remoteJid: $number");
}

if (!$number) {
    debug_log("Could not extract phone number from remoteJid or remoteJidAlt");
    exit("❌ Invalid remoteJid format.");
}

debug_log("Final phone number to send: $number");

// 3. Extract a supported video link (Pinterest, Facebook, Instagram, YouTube, etc.)
$videoRegexes = [
    // Pinterest
    '#(https://pin\.it/[a-zA-Z0-9]+|https://(?:[a-z]+\.)?pinterest\.[a-z]+/pin/\d+/?)#',
    // Facebook
    '#https://(www\.)?facebook\.[a-z]+/[^ ]+#',
    // Instagram
    '#https://(www\.)?instagram\.[a-z]+/[^ ]+#',
    // TeraBox
    '#https?://(?:[A-Za-z0-9\.-]*terabox[A-Za-z0-9\.-]*\.[A-Za-z]{2,})(?:/[^ ]*)?#',
    // YouTube Shorts
    '#https:\/\/(?:www\.)?youtube\.com\/shorts\/[\w\-]+#',
    // YouTube Regular videos
    '#https:\/\/(?:www\.)?youtube\.com\/watch\?v=[\w\-]+#'
];

$linkFound = '';
foreach ($videoRegexes as $regex) {
    if (preg_match($regex, $messageText, $matches)) {
        $linkFound = $matches[0];
        break;
    }
}

if (!$linkFound) {
    debug_log("No supported video link found in: $messageText");
    exit("❌ No supported video link found in message.");
}
debug_log("Detected video URL: $linkFound");

// 4. Use a single downloader API for all links
$apiUrl = API_BASE . '?url=' . urlencode($linkFound);

// 5. Fetch video using cURL
$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
curl_setopt($ch, CURLOPT_TIMEOUT, 40);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

$apiResponse = curl_exec($ch);
if (curl_errno($ch)) {
    $error_msg = curl_error($ch);
    curl_close($ch);
    debug_log("Downloader API curl error: $error_msg");
    exit("❌ Downloader API curl error: $error_msg");
}
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

debug_log("Downloader API HTTP code: $http_code\nDownloader API Response: $apiResponse");

if ($http_code !== 200) {
    debug_log("Downloader API HTTP error: $http_code");
    exit("❌ Downloader API HTTP error: $http_code");
}

$responseData = json_decode($apiResponse, true);
if (
    !$responseData ||
    !isset($responseData['status']) ||
    $responseData['status'] !== 'success' ||
    empty($responseData['media_url'])
) {
    debug_log("Failed Downloader API or bad response: $apiResponse");
    exit("❌ Failed to fetch video. Raw response: " . $apiResponse);
}

$MediaUrl = $responseData['media_url'];
$title = $responseData['title'] ?? 'Video';
debug_log("Video URL: $MediaUrl, Title: $title");

// 6. Send to WhatsApp API using cURL
$payload = [
    "number" => $number,
    "type" => "media",
    "message" => $title,
    "media_url" => $MediaUrl,
    "instance_id" => WHATSAPP_INSTANCE_ID,
    "access_token" => WHATSAPP_ACCESS_TOKEN
];

debug_log("WhatsApp API Payload: " . json_encode($payload));

$ch = curl_init("https://textsnap.in/api/send");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Accept: application/json"
]);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

$result = curl_exec($ch);
if (curl_errno($ch)) {
    $error_msg = curl_error($ch);
    curl_close($ch);
    debug_log("WhatsApp API curl error: $error_msg");
    exit("❌ WhatsApp API curl error: $error_msg");
}
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$redirect_url = curl_getinfo($ch, CURLINFO_REDIRECT_URL);
curl_close($ch);

debug_log("WhatsApp API HTTP code: $http_code");
debug_log("WhatsApp API Redirect URL: $redirect_url");
debug_log("WhatsApp API response: $result");

if ($http_code !== 200 && $http_code !== 201) {
    debug_log("WhatsApp API HTTP error: $http_code");
    exit("❌ WhatsApp API HTTP error: $http_code\nRaw response: $result");
}

debug_log("✅ Video sent successfully!");
echo json_encode([
    "status" => "success",
    "message" => "✅ Video sent successfully!",
    "phone_number" => $number,
    "video_url" => $linkFound,
    "http_code" => $http_code
]);
?>
