<?php
/**
 * FAQ AI-Powered Search API
 * Sprint 4: Semantic search using Gemini API + FULLTEXT fallback
 * 
 * @version 1.0
 * @date 2025-11-23
 */

header('Content-Type: application/json');

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../env.php';

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['query']) || empty(trim($input['query']))) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing search query']);
    exit;
}

$query = trim($input['query']);
$language = isset($input['language']) && $input['language'] === 'en' ? 'en' : 'da';

/**
 * AI-Powered Semantic Search using Gemini API
 * Analyzes the query and matches against FAQ keywords
 */
function searchWithAI($query, $language, $pdo) {
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
    $faqs_context = array_map(function($faq) {
        return "ID: {$faq['id']}, Question: {$faq['question']}, Keywords: " . $faq['keywords'];
    }, $all_faqs);
    
    $prompt = "You are a helpful FAQ search assistant for a cybersecurity company. Analyze this user query and return the IDs of the most relevant FAQ items (maximum 5) in JSON format.\n\n";
    $prompt .= "User Query: \"$query\"\n\n";
    $prompt .= "Available FAQs:\n" . implode("\n", array_slice($faqs_context, 0, 20)); // Limit context
    $prompt .= "\n\nRespond with ONLY a JSON array of FAQ IDs, ordered by relevance: [1, 5, 3]";
    
    // Call Gemini API
    $ch = curl_init('https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=' . $GEMINI_API_KEY);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
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
function searchWithFulltext($query, $language, $pdo) {
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
function searchWithKeywords($query, $language, $pdo) {
    $keywords = array_filter(explode(' ', strtolower($query)));
    
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
    $results = searchWithAI($query, $language, $pdo);
    $search_method = 'ai';
    
    // Fall back to FULLTEXT if AI fails
    if ($results === null || empty($results)) {
        $results = searchWithFulltext($query, $language, $pdo);
        $search_method = 'fulltext';
    }
    
    // Last resort: keyword matching
    if (empty($results)) {
        $results = searchWithKeywords($query, $language, $pdo);
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
