<?php

declare(strict_types=1);

error_reporting(E_ALL & ~E_WARNING);

$tmpRoot = __DIR__ . '/tmp_blog_test';
if (!is_dir($tmpRoot)) {
    mkdir($tmpRoot, 0777, true);
}
if (!is_dir($tmpRoot . '/blog')) {
    mkdir($tmpRoot . '/blog', 0777, true);
}

define('BBX_DATA_DIR', $tmpRoot);

require_once __DIR__ . '/../includes/blog-functions.php';

$tests_passed = 0;
$tests_failed = 0;

function assert_equals($expected, $actual, $message) {
    global $tests_passed, $tests_failed;
    if ($expected === $actual) {
        $tests_passed++;
        echo "✅ PASS: $message\n";
    } else {
        $tests_failed++;
        echo "❌ FAIL: $message\n";
        echo "   Expected: " . var_export($expected, true) . "\n";
        echo "   Actual:   " . var_export($actual, true) . "\n";
    }
}

function write_mock_json($data) {
    global $tmpRoot;
    $path = $tmpRoot . '/blog/posts.json';
    if ($data === null) {
        if (file_exists($path)) {
            unlink($path);
        }
    } else if (is_string($data)) {
        file_put_contents($path, $data);
    } else {
        file_put_contents($path, json_encode($data));
    }
}

// Fixture Data
$mock_posts = [
    [
        'id' => '1',
        'title' => 'Post 1',
        'region' => 'DK',
        'tags' => ['DDoS', 'Municipalities'],
        'published_at' => '2025-11-13T10:00:00Z'
    ],
    [
        'id' => '2',
        'title' => 'Post 2',
        'region' => 'EU',
        'tags' => ['Ransomware', 'Municipalities'],
        'published_at' => '2025-12-01T10:00:00Z'
    ],
    [
        'id' => '3',
        'title' => 'Post 3',
        'region' => 'DK',
        'tags' => ['Election'],
        'published_at' => '2025-10-15T10:00:00Z'
    ],
    [
        'id' => '4',
        'title' => 'Post 4',
        'region' => 'Global',
        'tags' => ['DDoS'],
        'published_at' => '2025-12-15T10:00:00Z'
    ]
];

$valid_json = [
    'version' => '1.0.0',
    'posts' => $mock_posts
];

// Test Case: Missing posts.json
write_mock_json(null);
$data = bbx_load_blog_posts_json();
assert_equals('1.0.0', $data['version'], 'Missing file should return fallback structure');
assert_equals([], $data['posts'], 'Missing file should have empty posts');
assert_equals(0, bbx_get_blog_posts_json_count(), 'Missing file count should be 0');

// Test Case: Malformed JSON
write_mock_json('{ malformed json');
$data = bbx_load_blog_posts_json();
assert_equals([], $data['posts'], 'Malformed JSON should return fallback structure with empty posts');
assert_equals(0, bbx_get_blog_posts_json_count(), 'Malformed JSON count should be 0');

// Test Case: Invalid shape (not an array/object)
write_mock_json('"string instead of object"');
$data = bbx_load_blog_posts_json();
assert_equals([], $data['posts'], 'Invalid root shape should return fallback structure');
assert_equals(0, bbx_get_blog_posts_json_count(), 'Invalid root shape count should be 0');

// Test Case: Invalid shape (posts is not an array)
write_mock_json(['version' => '1.0.0', 'posts' => 'not an array']);
assert_equals(0, bbx_get_blog_posts_json_count(), 'String posts key should be treated as empty array');
assert_equals([], bbx_get_blog_posts_from_json(), 'String posts key should return empty posts');

// Test Case: Missing posts key
write_mock_json(['version' => '1.0.0']);
$data_missing_posts = bbx_load_blog_posts_json();
assert_equals([], $data_missing_posts['posts'], 'Missing posts key should be merged to empty array in load_json');
assert_equals(0, bbx_get_blog_posts_json_count(), 'Missing posts key should have count 0');
assert_equals([], bbx_get_blog_posts_from_json(), 'Missing posts key should return empty array');

// Test Case: Missing root keys merged with fallback
write_mock_json(['posts' => []]);
$data_missing_root = bbx_load_blog_posts_json();
assert_equals('1.0.0', $data_missing_root['version'], 'Missing version key should be merged from fallback');

// Test Case: Empty posts
write_mock_json(['version' => '1.0.0', 'posts' => []]);
assert_equals(0, bbx_get_blog_posts_json_count(), 'Empty posts should have count 0');
assert_equals([], bbx_get_blog_posts_from_json(), 'Empty posts should return empty array');

// Test Case: Post with non-array tags
$invalid_tags_posts = [
    [
        'id' => '5',
        'title' => 'Post 5',
        'region' => 'US',
        'tags' => 'NotAnArray', // Invalid tags structure
        'published_at' => '2025-12-20T10:00:00Z'
    ]
];
write_mock_json(['version' => '1.0.0', 'posts' => $invalid_tags_posts]);
$data_invalid_tags = bbx_load_blog_posts_json();
assert_equals([], $data_invalid_tags['posts'][0]['tags'], 'Non-array tags should be normalized to [] explicitly on load');
assert_equals(1, bbx_get_blog_posts_json_count(), 'Should count post even with invalid tags when no tag filter is applied');
assert_equals(0, bbx_get_blog_posts_json_count(null, 'NotAnArray'), 'Should safely ignore non-array tags during filtering');
$posts_res = bbx_get_blog_posts_from_json(1, 10, null, 'NotAnArray');
assert_equals([], $posts_res, 'Should safely ignore non-array tags during filtering');

// Test Case: Root shape is a plain list (invalid shape)
write_mock_json([1, 2, 3]);
$data = bbx_load_blog_posts_json();
assert_equals([], $data['posts'], 'Root list shape should return fallback structure');
assert_equals(0, bbx_get_blog_posts_json_count(), 'Root list shape count should be 0');

// Test Case: Unreadable file
if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') { // chmod behaves differently on Windows
    $unreadable_path = $tmpRoot . '/blog/posts.json';
    write_mock_json('{"version":"1.0.0", "posts":[]}');
    // Try to make unreadable
    chmod($unreadable_path, 0000);
    // If the file is actually unreadable (might still be readable if running as root)
    if (@file_get_contents($unreadable_path) === false) {
        $data = bbx_load_blog_posts_json();
        assert_equals([], $data['posts'], 'Unreadable file should return fallback structure');
    } else {
        echo "⚠️ SKIP: Environment allows reading file with 0000 permissions (likely running as root).\n";
    }
    // Restore permissions so cleanup works
    chmod($unreadable_path, 0644);
} else {
    echo "⚠️ SKIP: Unreadable file test skipped on Windows.\n";
}

// Setup valid data for remaining tests
write_mock_json($valid_json);

// Test Case: no filter = korrekt total count
assert_equals(4, bbx_get_blog_posts_json_count(), 'Count without filters should be 4');

// Test Case: region filter
assert_equals(2, bbx_get_blog_posts_json_count('DK'), 'Count with region=DK should be 2');
assert_equals(1, bbx_get_blog_posts_json_count('EU'), 'Count with region=EU should be 1');

// Test Case: tag filter
assert_equals(2, bbx_get_blog_posts_json_count(null, 'DDoS'), 'Count with tag=DDoS should be 2');
assert_equals(2, bbx_get_blog_posts_json_count(null, 'Municipalities'), 'Count with tag=Municipalities should be 2');

// Test Case: kombineret region + tag filter
assert_equals(1, bbx_get_blog_posts_json_count('DK', 'DDoS'), 'Count with region=DK and tag=DDoS should be 1');

// Test Case: ukendt region/tag giver 0 eller tom liste
assert_equals(0, bbx_get_blog_posts_json_count('UnknownRegion'), 'Count with unknown region should be 0');
assert_equals(0, bbx_get_blog_posts_json_count(null, 'UnknownTag'), 'Count with unknown tag should be 0');
assert_equals([], bbx_get_blog_posts_from_json(1, 10, 'UnknownRegion'), 'Posts with unknown region should be empty');

// Test Case: sortering efter published_at descending
$posts = bbx_get_blog_posts_from_json(1, 10);
assert_equals('Post 4', $posts[0]['title'], 'First post should be the newest (Post 4)');
assert_equals('Post 2', $posts[1]['title'], 'Second post should be Post 2');
assert_equals('Post 1', $posts[2]['title'], 'Third post should be Post 1');
assert_equals('Post 3', $posts[3]['title'], 'Fourth post should be the oldest (Post 3)');

// Test Case: pagination med page og per_page
$page1 = bbx_get_blog_posts_from_json(1, 2);
assert_equals(2, count($page1), 'Page 1 should have 2 posts');
assert_equals('Post 4', $page1[0]['title'], 'Page 1 first post should be Post 4');
assert_equals('Post 2', $page1[1]['title'], 'Page 1 second post should be Post 2');

$page2 = bbx_get_blog_posts_from_json(2, 2);
assert_equals(2, count($page2), 'Page 2 should have 2 posts');
assert_equals('Post 1', $page2[0]['title'], 'Page 2 first post should be Post 1');
assert_equals('Post 3', $page2[1]['title'], 'Page 2 second post should be Post 3');

$page3 = bbx_get_blog_posts_from_json(3, 2);
assert_equals([], $page3, 'Page 3 should be empty');

// Test Case: tags returneres unikke og sorteret
$tags = bbx_get_blog_tags_from_json();
assert_equals(['DDoS', 'Election', 'Municipalities', 'Ransomware'], $tags, 'Tags should be unique and sorted');

// Test Case: regions returneres unikke og sorteret
$regions = bbx_get_blog_regions_from_json();
assert_equals(['DK', 'EU', 'Global'], $regions, 'Regions should be unique and sorted');

// Clean up
array_map('unlink', glob("$tmpRoot/blog/*.*"));
rmdir("$tmpRoot/blog");
rmdir($tmpRoot);

echo "\nTests Complete: $tests_passed Passed, $tests_failed Failed\n";

if ($tests_failed > 0) {
    exit(1);
} else {
    exit(0);
}
