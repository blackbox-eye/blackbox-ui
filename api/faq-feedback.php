<?php
/**
 * FAQ Feedback API - Helpfulness Voting
 * Sprint 4: Track user feedback on FAQ helpfulness
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

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['faq_id']) || !isset($input['vote'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required parameters']);
    exit;
}

$faq_id = (int) $input['faq_id'];
$vote = $input['vote'];

// Validate vote type
if (!in_array($vote, ['helpful', 'not-helpful'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid vote type']);
    exit;
}

try {
    // Update the appropriate counter
    $column = $vote === 'helpful' ? 'helpful_count' : 'not_helpful_count';
    
    $sql = "UPDATE faq_items SET $column = $column + 1 WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $faq_id]);
    
    // Get updated counts
    $select_sql = "SELECT helpful_count, not_helpful_count FROM faq_items WHERE id = :id";
    $select_stmt = $pdo->prepare($select_sql);
    $select_stmt->execute(['id' => $faq_id]);
    $counts = $select_stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($counts) {
        echo json_encode([
            'success' => true,
            'helpful_count' => (int) $counts['helpful_count'],
            'not_helpful_count' => (int) $counts['not_helpful_count']
        ]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'FAQ not found']);
    }
    
} catch (PDOException $e) {
    error_log('FAQ Feedback Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
