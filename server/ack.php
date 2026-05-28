<?php
define('EXPECTED_HASH', '991d92f65da1');
define('LOG_FILE', __DIR__ . '/hits.log');
define('TELEGRAM_TOKEN', getenv('TELEGRAM_TOKEN'));
define('TELEGRAM_CHAT_ID', getenv('TELEGRAM_CHAT_ID'));

function get_all_headers_safe(): array {
    $headers = [];
    foreach ($_SERVER as $key => $val) {
        if (str_starts_with($key, 'HTTP_')) {
            $name = str_replace('_', '-', substr($key, 5));
            $headers[$name] = $val;
        }
    }
    return $headers;
}

function is_interesting(array $headers, string $path): bool {
    // Correct hash path = always log
    if (str_contains($path, EXPECTED_HASH)) return true;

    // Has our custom headers = autonomous agent responded
    if (isset($headers['X-AGENT-TYPE']) || isset($headers['X-CAPABILITY'])) return true;

    // Very sparse headers (< 5) = probably an agent, not a browser
    if (count($headers) < 5) return true;

    return false;
}

function get_asn(string $ip): string {
    $url = "https://ipapi.co/{$ip}/org/";
    $ctx = stream_context_create(['http' => ['timeout' => 3]]);
    $result = @file_get_contents($url, false, $ctx);
    return $result ?: 'unknown';
}

function send_telegram(string $message): void {
    if (!TELEGRAM_TOKEN || !TELEGRAM_CHAT_ID) return;
    $url = "https://api.telegram.org/bot" . TELEGRAM_TOKEN . "/sendMessage";
    $payload = json_encode(['chat_id' => TELEGRAM_CHAT_ID, 'text' => $message, 'parse_mode' => 'Markdown']);
    $ctx = stream_context_create([
        'http' => [
            'method'  => 'POST',
            'header'  => "Content-Type: application/json\r\n",
            'content' => $payload,
            'timeout' => 5
        ]
    ]);
    @file_get_contents($url, false, $ctx);
}

// --- Main ---
$ip      = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$path    = $_SERVER['REQUEST_URI'] ?? '/';
$ua      = $_SERVER['HTTP_USER_AGENT'] ?? '';
$headers = get_all_headers_safe();
$time    = date('Y-m-d H:i:s');

if (!is_interesting($headers, $path)) {
    http_response_code(200);
    exit;
}

$asn       = get_asn($ip);
$hash_hit  = str_contains($path, EXPECTED_HASH);
$agent_hdr = $headers['X-AGENT-TYPE'] ?? null;
$cap_hdr   = $headers['X-CAPABILITY'] ?? null;

$entry = [
    'time'       => $time,
    'ip'         => $ip,
    'asn'        => $asn,
    'path'       => $path,
    'ua'         => $ua,
    'headers'    => count($headers),
    'hash_match' => $hash_hit,
    'agent_type' => $agent_hdr,
    'capability' => $cap_hdr,
    'all_headers'=> $headers,
];

file_put_contents(LOG_FILE, json_encode($entry) . "\n", FILE_APPEND | LOCK_EX);

// Notify via Telegram if hash matched or agent identified itself
if ($hash_hit || $agent_hdr) {
    $label = $hash_hit ? '🎯 *HASH MATCH*' : '🤖 *AGENT HEADER*';
    $msg = "{$label}\n"
        . "Time: `{$time}`\n"
        . "IP: `{$ip}`\n"
        . "ASN: `{$asn}`\n"
        . "Agent-Type: `" . ($agent_hdr ?? 'not set') . "`\n"
        . "Capability: `" . ($cap_hdr ?? 'not set') . "`\n"
        . "UA: `{$ua}`\n"
        . "Headers sent: `{$entry['headers']}`";
    send_telegram($msg);
}

http_response_code(200);
header('Content-Type: application/json');
echo json_encode(['status' => 'acknowledged', 'time' => $time]);
