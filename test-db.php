<?php
/**
 * Database Connection Test
 * 
 * IMPORTANT: DELETE THIS FILE AFTER TESTING!
 * This file exposes database connection status.
 */

require_once 'db.php';

// Set plain text output
header('Content-Type: text/plain; charset=utf-8');

echo "========================================\n";
echo "DATABASE CONNECTION TEST\n";
echo "========================================\n\n";

echo "Date: " . date('Y-m-d H:i:s') . "\n";
echo "Server: " . $_SERVER['HTTP_HOST'] . "\n\n";

// Check if BBX_DB_CONNECTED is defined
if (defined('BBX_DB_CONNECTED')) {
    echo "BBX_DB_CONNECTED: " . (BBX_DB_CONNECTED ? 'true' : 'false') . "\n";
    
    if (BBX_DB_CONNECTED) {
        echo "Status: ✅ Connected successfully\n\n";
        
        // Test database query
        try {
            $stmt = $pdo->query("SELECT DATABASE() as dbname, VERSION() as version");
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo "Database Name: " . $row['dbname'] . "\n";
            echo "MySQL Version: " . $row['version'] . "\n";
            
            // Test table access
            echo "\nTesting table access...\n";
            
            $tables = ['faq_items', 'blog_posts'];
            foreach ($tables as $table) {
                try {
                    $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
                    $count = $stmt->fetch(PDO::FETCH_ASSOC);
                    echo "  ✅ $table: {$count['count']} rows\n";
                } catch (PDOException $e) {
                    echo "  ⚠️ $table: Not accessible\n";
                }
            }
            
        } catch (PDOException $e) {
            echo "\n❌ Query test failed:\n";
            echo "Error: " . $e->getMessage() . "\n";
        }
        
    } else {
        echo "Status: ❌ Connection failed\n\n";
        
        if (defined('BBX_DB_ERROR_MESSAGE')) {
            echo "Error Message:\n";
            echo BBX_DB_ERROR_MESSAGE . "\n\n";
            
            echo "Troubleshooting:\n";
            echo "1. Check MySQL credentials in db.php\n";
            echo "2. Verify MySQL service is running\n";
            echo "3. Check firewall allows database connections\n";
            echo "4. Review server error logs\n";
        }
    }
} else {
    echo "Status: ⚠️ BBX_DB_CONNECTED not defined\n\n";
    echo "This means db.php may not be loaded correctly.\n";
    echo "Check that db.php exists in the same directory.\n";
}

echo "\n========================================\n";
echo "⚠️ SECURITY WARNING\n";
echo "========================================\n";
echo "DELETE this file immediately after testing!\n";
echo "This file exposes database connection status.\n";
echo "\n";
?>
