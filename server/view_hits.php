<?php
// Simple log viewer — only accessible with ?key=
define('VIEW_KEY', getenv('VIEW_KEY') ?: 'changeme');
define('LOG_FILE', __DIR__ . '/hits.log');

if (($_GET['key'] ?? '') !== VIEW_KEY) {
    http_response_code(403);
    exit('Forbidden');
}

$filter = $_GET['filter'] ?? 'all'; // all | hash | agent

$lines = file_exists(LOG_FILE) ? file(LOG_FILE, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];
$hits  = array_map('json_decode', $lines);

if ($filter === 'hash')  $hits = array_filter($hits, fn($h) => $h->hash_match);
if ($filter === 'agent') $hits = array_filter($hits, fn($h) => $h->agent_type);

header('Content-Type: text/plain');
echo "=== AI BEHAVIOR RESEARCH — HIT LOG ===\n";
echo "Total: " . count($hits) . " | Filter: {$filter}\n\n";

foreach (array_reverse(array_values($hits)) as $h) {
    $flag = $h->hash_match ? '[HASH]' : ($h->agent_type ? '[AGENT]' : '[SPARSE]');
    echo "{$flag} {$h->time} | {$h->ip} | {$h->asn}\n";
    if ($h->agent_type) echo "  Agent-Type: {$h->agent_type}\n";
    if ($h->capability) echo "  Capability: {$h->capability}\n";
    if ($h->ua)         echo "  UA: {$h->ua}\n";
    echo "\n";
}
