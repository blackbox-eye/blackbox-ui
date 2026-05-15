<?php

declare(strict_types=1);

/**
 * FAQ AI-Powered Search API
 * Sprint 4: Semantic search using Gemini API + FULLTEXT fallback
 *
 * @version 1.0
 * @date 2025-11-23
 */

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('X-Content-Type-Options: nosniff');

const BBX_FAQ_SEARCH_MAX_BODY_BYTES = 4096;
const BBX_FAQ_SEARCH_MAX_QUERY_LENGTH = 300;
const BBX_FAQ_SEARCH_RATE_LIMIT_WINDOW = 300;
const BBX_FAQ_SEARCH_RATE_LIMIT_MAX_ATTEMPTS = 60;
const BBX_FAQ_SEARCH_RATE_LIMIT_MAX_IPS = 500;
const BBX_FAQ_SEARCH_TIMEOUT = 5;
const BBX_FAQ_SEARCH_CONNECT_TIMEOUT = 3;

function bbx_faq_search_declared_body_too_large(int $maxBytes): bool
{
  $contentLength = $_SERVER['CONTENT_LENGTH'] ?? null;
  if (!is_scalar($contentLength) || !is_numeric((string) $contentLength)) {
    return false;
  }

  return (int) $contentLength > $maxBytes;
}

function bbx_faq_search_read_bounded_body(int $maxBytes, bool &$tooLarge = false): ?string
{
  $tooLarge = false;
  $stream = fopen('php://input', 'rb');
  if ($stream === false) {
    return null;
  }

  $body = stream_get_contents($stream, $maxBytes + 1);
  fclose($stream);

  if (!is_string($body)) {
    return null;
  }

  if (strlen($body) > $maxBytes) {
    $tooLarge = true;
    return null;
  }

  return $body;
}

function bbx_faq_search_log_security(string $event, array $context = []): void
{
  $pairs = [];
  foreach ($context as $key => $value) {
    if (!is_scalar($value) || $value === '') {
      continue;
    }
    $pairs[] = $key . '=' . (string) $value;
  }

  error_log('faq-search security: ' . $event . (empty($pairs) ? '' : ' ' . implode(' ', $pairs)));
}

function bbx_faq_search_normalize_host(string $host): string
{
  $host = strtolower(trim($host));
  if ($host === '') {
    return '';
  }

  if ($host[0] === '[') {
    $end = strpos($host, ']');
    if ($end !== false) {
      return substr($host, 1, $end - 1);
    }
  }

  if (substr_count($host, ':') === 1) {
    $parts = explode(':', $host, 2);
    return $parts[0];
  }

  return $host;
}

function bbx_faq_search_normalize_port(?int $port): ?int
{
  if ($port === null || $port === 80 || $port === 443) {
    return null;
  }

  return $port > 0 ? $port : null;
}

function bbx_faq_search_parse_origin(string $value): ?array
{
  $parts = parse_url(trim($value));
  if (!is_array($parts) || empty($parts['host'])) {
    return null;
  }

  return [
    'host' => bbx_faq_search_normalize_host((string) $parts['host']),
    'port' => bbx_faq_search_normalize_port(isset($parts['port']) ? (int) $parts['port'] : null),
  ];
}

function bbx_faq_search_current_origin(): ?array
{
  $hostHeader = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '';
  if (!is_string($hostHeader) || trim($hostHeader) === '') {
    return null;
  }

  $parts = parse_url('//' . trim($hostHeader));
  if (!is_array($parts) || empty($parts['host'])) {
    return null;
  }

  return [
    'host' => bbx_faq_search_normalize_host((string) $parts['host']),
    'port' => bbx_faq_search_normalize_port(isset($parts['port']) ? (int) $parts['port'] : null),
  ];
}

function bbx_faq_search_is_allowed_request_origin(): bool
{
  $currentOrigin = bbx_faq_search_current_origin();
  if ($currentOrigin === null || $currentOrigin['host'] === '') {
    return true;
  }

  foreach (['HTTP_ORIGIN', 'HTTP_REFERER'] as $headerName) {
    $headerValue = $_SERVER[$headerName] ?? '';
    if (!is_string($headerValue) || trim($headerValue) === '') {
      continue;
    }

    $requestOrigin = bbx_faq_search_parse_origin($headerValue);
    if ($requestOrigin === null) {
      return false;
    }

    if ($requestOrigin['host'] !== $currentOrigin['host'] || $requestOrigin['port'] !== $currentOrigin['port']) {
      return false;
    }
  }

  return true;
}

function bbx_faq_search_client_ip(): string
{
  $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
  $ip = is_string($ip) ? trim($ip) : 'unknown';
  return $ip === '' ? 'unknown' : substr($ip, 0, 64);
}

function bbx_faq_search_normalize_text($value, int $maxLength): string
{
  if (!is_scalar($value)) {
    return '';
  }

  $text = trim((string) $value);
  $text = preg_replace('/[\x00-\x1F\x7F]+/u', ' ', $text) ?? $text;
  $text = preg_replace('/\s+/u', ' ', $text) ?? $text;

  if (function_exists('mb_substr')) {
    return mb_substr($text, 0, $maxLength);
  }

  return substr($text, 0, $maxLength);
}

function bbx_faq_search_load_rate_limit_store(string $file): array
{
  if (!file_exists($file)) {
    return [];
  }

  $data = json_decode((string) @file_get_contents($file), true);
  return is_array($data) ? $data : [];
}

function bbx_faq_search_save_rate_limit_store(string $file, array $store): bool
{
  $latestByIp = [];
  foreach ($store as $ip => $timestamps) {
    if (!is_array($timestamps) || empty($timestamps)) {
      unset($store[$ip]);
      continue;
    }
    $latestByIp[$ip] = max($timestamps);
  }

  arsort($latestByIp);
  $allowedIps = array_slice(array_keys($latestByIp), 0, BBX_FAQ_SEARCH_RATE_LIMIT_MAX_IPS);
  $boundedStore = [];
  foreach ($allowedIps as $ip) {
    $boundedStore[$ip] = array_values(array_slice($store[$ip], -BBX_FAQ_SEARCH_RATE_LIMIT_MAX_ATTEMPTS));
  }

  $encodedStore = json_encode($boundedStore, JSON_PRETTY_PRINT | JSON_INVALID_UTF8_SUBSTITUTE);
  if (!is_string($encodedStore)) {
    return false;
  }

  return @file_put_contents($file, $encodedStore, LOCK_EX) !== false;
}

function bbx_faq_search_enforce_rate_limit(string $file, ?int &$retryAfter = null): bool
{
  $retryAfter = null;
  $now = time();
  $windowStart = $now - BBX_FAQ_SEARCH_RATE_LIMIT_WINDOW;
  $ip = bbx_faq_search_client_ip();
  $store = bbx_faq_search_load_rate_limit_store($file);

  foreach ($store as $storedIp => $timestamps) {
    if (!is_array($timestamps)) {
      unset($store[$storedIp]);
      continue;
    }

    $filtered = [];
    foreach ($timestamps as $timestamp) {
      $timestamp = (int) $timestamp;
      if ($timestamp >= $windowStart) {
        $filtered[] = $timestamp;
      }
    }

    if (empty($filtered)) {
      unset($store[$storedIp]);
      continue;
    }

    $store[$storedIp] = $filtered;
  }

  $attempts = $store[$ip] ?? [];
  if (count($attempts) >= BBX_FAQ_SEARCH_RATE_LIMIT_MAX_ATTEMPTS) {
    if (!bbx_faq_search_save_rate_limit_store($file, $store)) {
      return false;
    }
    $oldestAttempt = min($attempts);
    $retryAfter = max(1, BBX_FAQ_SEARCH_RATE_LIMIT_WINDOW - ($now - $oldestAttempt));
    return true;
  }

  $attempts[] = $now;
  $store[$ip] = $attempts;
  if (!bbx_faq_search_save_rate_limit_store($file, $store)) {
    return false;
  }

  return true;
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['error' => 'Method not allowed']);
  exit;
}

if (!bbx_faq_search_is_allowed_request_origin()) {
  bbx_faq_search_log_security('origin_rejected', ['ip' => bbx_faq_search_client_ip()]);
  http_response_code(403);
  echo json_encode(['error' => 'Request origin not allowed']);
  exit;
}

$rateLimitFile = __DIR__ . '/../logs/faq-search-throttle.json';
$rateLimitDir = dirname($rateLimitFile);
if (!is_dir($rateLimitDir) && !@mkdir($rateLimitDir, 0755, true) && !is_dir($rateLimitDir)) {
  bbx_faq_search_log_security('throttle_store_unavailable');
  http_response_code(500);
  echo json_encode(['success' => false, 'error' => 'Search failed', 'results' => []]);
  exit;
}

$retryAfter = null;
if (!bbx_faq_search_enforce_rate_limit($rateLimitFile, $retryAfter)) {
  bbx_faq_search_log_security('throttle_store_write_failed', ['ip' => bbx_faq_search_client_ip()]);
  http_response_code(500);
  echo json_encode(['success' => false, 'error' => 'Search failed', 'results' => []]);
  exit;
}

if ($retryAfter !== null) {
  bbx_faq_search_log_security('rate_limited', ['ip' => bbx_faq_search_client_ip()]);
  header('Retry-After: ' . $retryAfter);
  http_response_code(429);
  echo json_encode(['error' => 'Too many requests']);
  exit;
}

if (bbx_faq_search_declared_body_too_large(BBX_FAQ_SEARCH_MAX_BODY_BYTES)) {
  bbx_faq_search_log_security('body_too_large', ['ip' => bbx_faq_search_client_ip()]);
  http_response_code(413);
  echo json_encode(['error' => 'Request body too large']);
  exit;
}

$bodyTooLarge = false;
$rawInput = bbx_faq_search_read_bounded_body(BBX_FAQ_SEARCH_MAX_BODY_BYTES, $bodyTooLarge);
if ($bodyTooLarge) {
  bbx_faq_search_log_security('body_too_large', ['ip' => bbx_faq_search_client_ip()]);
  http_response_code(413);
  echo json_encode(['error' => 'Request body too large']);
  exit;
}

if ($rawInput === null) {
  bbx_faq_search_log_security('body_read_failed', ['ip' => bbx_faq_search_client_ip()]);
  http_response_code(500);
  echo json_encode(['success' => false, 'error' => 'Search failed', 'results' => []]);
  exit;
}

$input = json_decode($rawInput, true);
if (!is_array($input)) {
  bbx_faq_search_log_security('invalid_json', ['ip' => bbx_faq_search_client_ip()]);
  $input = [];
}

$query = bbx_faq_search_normalize_text($input['query'] ?? '', BBX_FAQ_SEARCH_MAX_QUERY_LENGTH);
if ($query === '') {
  http_response_code(400);
  echo json_encode(['error' => 'Missing search query']);
  exit;
}

$language = isset($input['language']) && $input['language'] === 'en' ? 'en' : 'da';

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../env.php';

if (!isset($pdo) || !($pdo instanceof PDO)) {
  bbx_faq_search_log_security('database_unavailable');
  http_response_code(500);
  echo json_encode(['success' => false, 'error' => 'Search failed', 'results' => []]);
  exit;
}

$faqSearchPdo = $pdo;

/**
 * AI-Powered Semantic Search using Gemini API
 * Analyzes the query and matches against FAQ keywords
 */
function searchWithAI($query, $language, $pdo)
{
  global $GEMINI_API_KEY;

  if (empty($GEMINI_API_KEY)) {
    return null; // Fall back to traditional search
  }

  // Get all FAQs with keywords
  $sql = "
        SELECT
            id,
            category,
            " . ($language === 'da' ? 'question_da AS question' : 'question_en AS question') . ",
            " . ($language === 'da' ? 'answer_da AS answer' : 'answer_en AS answer') . ",
            keywords,
            helpful_count,
            not_helpful_count
        FROM faq_items
        ORDER BY helpful_count DESC
    ";

  $stmt = $pdo->query($sql);
  $all_faqs = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // Prepare AI prompt
  $faqs_context = array_map(function ($faq) {
    return "ID: {$faq['id']}, Question: {$faq['question']}, Keywords: " . $faq['keywords'];
  }, $all_faqs);

  $prompt = "You are a helpful FAQ search assistant for a cybersecurity company. Analyze this user query and return the IDs of the most relevant FAQ items (maximum 5) in JSON format.\n\n";
  $prompt .= "User Query: \"$query\"\n\n";
  $prompt .= "Available FAQs:\n" . implode("\n", array_slice($faqs_context, 0, 20)); // Limit context
  $prompt .= "\n\nRespond with ONLY a JSON array of FAQ IDs, ordered by relevance: [1, 5, 3]";

  // Call Gemini API
  $ch = curl_init('https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=' . $GEMINI_API_KEY);
  if ($ch === false) {
    error_log('FAQ Search Error: unable to initialize Gemini request');
    return null;
  }

  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_TIMEOUT => BBX_FAQ_SEARCH_TIMEOUT,
    CURLOPT_CONNECTTIMEOUT => BBX_FAQ_SEARCH_CONNECT_TIMEOUT,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_POSTFIELDS => json_encode([
      'contents' => [['parts' => [['text' => $prompt]]]],
      'generationConfig' => [
        'temperature' => 0.3,
        'maxOutputTokens' => 200
      ]
    ])
  ]);

  $response = curl_exec($ch);
  if ($response === false) {
    error_log('FAQ Search Error: Gemini request failed');
    curl_close($ch);
    return null;
  }

  $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);

  if ($http_code !== 200) {
    error_log("Gemini API Error: HTTP $http_code");
    return null;
  }

  $data = json_decode($response, true);

  if (!isset($data['candidates'][0]['content']['parts'][0]['text'])) {
    return null;
  }

  $ai_response = $data['candidates'][0]['content']['parts'][0]['text'];

  // Extract JSON array from response
  preg_match('/\[[\d,\s]+\]/', $ai_response, $matches);

  if (empty($matches)) {
    return null;
  }

  $relevant_ids = json_decode($matches[0], true);

  if (!is_array($relevant_ids) || empty($relevant_ids)) {
    return null;
  }

  $relevant_ids = array_values(array_unique(array_filter(array_map(static function ($value) {
    $id = filter_var($value, FILTER_VALIDATE_INT);
    return $id !== false && $id > 0 ? (int) $id : null;
  }, $relevant_ids))));
  $relevant_ids = array_slice($relevant_ids, 0, 5);

  if ($relevant_ids === []) {
    return null;
  }

  // Fetch the relevant FAQs in order
  $placeholders = implode(',', array_fill(0, count($relevant_ids), '?'));
  $ordered_sql = "
        SELECT
            id,
            category,
            " . ($language === 'da' ? 'question_da AS question' : 'question_en AS question') . ",
            " . ($language === 'da' ? 'answer_da AS answer' : 'answer_en AS answer') . "
        FROM faq_items
        WHERE id IN ($placeholders)
        ORDER BY FIELD(id, " . implode(',', $relevant_ids) . ")
    ";

  $stmt = $pdo->prepare($ordered_sql);
  $stmt->execute($relevant_ids);

  return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Traditional FULLTEXT Search (Fallback)
 */
function searchWithFulltext($query, $language, $pdo)
{
  $sql = "
        SELECT
            id,
            category,
            " . ($language === 'da' ? 'question_da AS question' : 'question_en AS question') . ",
            " . ($language === 'da' ? 'answer_da AS answer' : 'answer_en AS answer') . ",
            MATCH(question_da, question_en, answer_da, answer_en) AGAINST(:query IN NATURAL LANGUAGE MODE) AS relevance
        FROM faq_items
        WHERE MATCH(question_da, question_en, answer_da, answer_en) AGAINST(:query IN NATURAL LANGUAGE MODE)
        ORDER BY relevance DESC, helpful_count DESC
        LIMIT 5
    ";

  $stmt = $pdo->prepare($sql);
  $stmt->execute(['query' => $query]);

  return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Simple keyword matching (last resort)
 */
function searchWithKeywords($query, $language, $pdo)
{
  $keywords = array_values(array_slice(array_filter(explode(' ', strtolower($query))), 0, 8));

  if (empty($keywords)) {
    return [];
  }

  // Build LIKE conditions
  $conditions = [];
  $params = [];

  foreach ($keywords as $i => $keyword) {
    $param = "keyword_$i";
    $conditions[] = "(" .
      ($language === 'da' ? 'LOWER(question_da)' : 'LOWER(question_en)') . " LIKE :$param OR " .
      ($language === 'da' ? 'LOWER(answer_da)' : 'LOWER(answer_en)') . " LIKE :$param OR " .
      "LOWER(keywords) LIKE :$param)";
    $params[$param] = "%$keyword%";
  }

  $sql = "
        SELECT
            id,
            category,
            " . ($language === 'da' ? 'question_da AS question' : 'question_en AS question') . ",
            " . ($language === 'da' ? 'answer_da AS answer' : 'answer_en AS answer') . "
        FROM faq_items
        WHERE " . implode(' AND ', $conditions) . "
        ORDER BY helpful_count DESC
        LIMIT 5
    ";

  $stmt = $pdo->prepare($sql);
  $stmt->execute($params);

  return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

try {
  // Try AI search first
  $results = searchWithAI($query, $language, $faqSearchPdo);
  $search_method = 'ai';

  // Fall back to FULLTEXT if AI fails
  if ($results === null || empty($results)) {
    $results = searchWithFulltext($query, $language, $faqSearchPdo);
    $search_method = 'fulltext';
  }

  // Last resort: keyword matching
  if (empty($results)) {
    $results = searchWithKeywords($query, $language, $faqSearchPdo);
    $search_method = 'keywords';
  }

  echo json_encode([
    'success' => true,
    'results' => $results,
    'count' => count($results),
    'search_method' => $search_method,
    'query' => $query
  ]);
} catch (Exception $e) {
  error_log('FAQ Search Error: ' . $e->getMessage());
  http_response_code(500);
  echo json_encode([
    'success' => false,
    'error' => 'Search failed',
    'results' => []
  ]);
}
