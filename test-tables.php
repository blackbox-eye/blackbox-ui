<?php

/**
 * Database Tables Test
 *
 * IMPORTANT: DELETE THIS FILE AFTER TESTING!
 * This file checks if required database tables exist.
 */

require_once 'db.php';

// Set plain text output
header('Content-Type: text/plain; charset=utf-8');

echo "========================================\n";
echo "DATABASE TABLES TEST\n";
echo "========================================\n\n";

echo "Date: " . date('Y-m-d H:i:s') . "\n";
echo "Server: " . $_SERVER['HTTP_HOST'] . "\n\n";

if (!defined('BBX_DB_CONNECTED') || !BBX_DB_CONNECTED) {
  echo "❌ Database not connected!\n";
  echo "Run test-db.php first to verify connection.\n";
  exit;
}

echo "Database connected successfully.\n\n";

// Required tables for Sprint 4
$requiredTables = [
  'faq_items' => [
    'description' => 'FAQ system with AI search',
    'schema_file' => 'db/schema/faq_items.sql'
  ],
  'blog_posts' => [
    'description' => 'Blog CMS with multi-language support',
    'schema_file' => 'db/schema/blog_posts.sql'
  ]
];

echo "Checking required tables...\n";
echo "----------------------------\n\n";

$allTablesExist = true;
$tableStats = [];

foreach ($requiredTables as $tableName => $tableInfo) {
  try {
    // Check if table exists
    $stmt = $pdo->query("SHOW TABLES LIKE '$tableName'");
    $exists = $stmt->rowCount() > 0;

    if ($exists) {
      // Get row count
      $stmt = $pdo->query("SELECT COUNT(*) as count FROM $tableName");
      $row = $stmt->fetch(PDO::FETCH_ASSOC);
      $rowCount = $row['count'];

      // Get table structure info
      $stmt = $pdo->query("DESCRIBE $tableName");
      $columnCount = $stmt->rowCount();

      echo "✅ $tableName: EXISTS\n";
      echo "   Description: {$tableInfo['description']}\n";
      echo "   Rows: $rowCount\n";
      echo "   Columns: $columnCount\n\n";

      $tableStats[$tableName] = [
        'exists' => true,
        'rows' => $rowCount,
        'columns' => $columnCount
      ];
    } else {
      echo "❌ $tableName: NOT FOUND\n";
      echo "   Description: {$tableInfo['description']}\n";
      echo "   Schema file: {$tableInfo['schema_file']}\n";
      echo "   Action: Import via phpMyAdmin or MySQL CLI\n\n";

      $allTablesExist = false;
      $tableStats[$tableName] = ['exists' => false];
    }
  } catch (PDOException $e) {
    echo "⚠️ $tableName: ERROR\n";
    echo "   Error: " . $e->getMessage() . "\n\n";

    $allTablesExist = false;
    $tableStats[$tableName] = ['exists' => false, 'error' => $e->getMessage()];
  }
}

// Summary
echo "========================================\n";
echo "SUMMARY\n";
echo "========================================\n\n";

if ($allTablesExist) {
  echo "✅ All required tables exist and are accessible!\n\n";

  echo "Table Statistics:\n";
  foreach ($tableStats as $table => $stats) {
    echo "  - $table: {$stats['rows']} rows, {$stats['columns']} columns\n";
  }

  echo "\nNext Steps:\n";
  echo "1. ✅ Tables verified\n";
  echo "2. ✅ Continue with Cloudflare cache clear\n";
  echo "3. ⚠️ DELETE this test file!\n";
} else {
  echo "❌ Some tables are missing!\n\n";

  echo "Import Instructions:\n\n";

  echo "Option A - Via phpMyAdmin:\n";
  echo "1. Login to phpMyAdmin\n";
  echo "2. Select database: blackowu_blackbox\n";
  echo "3. Click 'Import' tab\n";

  foreach ($requiredTables as $tableName => $tableInfo) {
    if (!$tableStats[$tableName]['exists']) {
      echo "4. Import file: {$tableInfo['schema_file']}\n";
    }
  }

  echo "\nOption B - Via MySQL CLI:\n";
  foreach ($requiredTables as $tableName => $tableInfo) {
    if (!$tableStats[$tableName]['exists']) {
      echo "mysql -u bbx_user -p blackowu_blackbox < {$tableInfo['schema_file']}\n";
    }
  }
  echo "\nAfter import:\n";
  echo "1. Refresh this page to verify\n";
  echo "2. ⚠️ DELETE this test file!\n";
}

echo "\n========================================\n";
echo "⚠️ SECURITY WARNING\n";
echo "========================================\n";
echo "DELETE this file immediately after testing!\n";
echo "This file exposes database structure information.\n";
echo "\n";
