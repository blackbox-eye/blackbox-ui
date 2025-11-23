#!/bin/bash
# ═══════════════════════════════════════════════════════════════════════════════
# Quick Fix Script - Upload db.php to Production
# ═══════════════════════════════════════════════════════════════════════════════

set -e

# Configuration
SSH_KEY="$HOME/.ssh/nexus-v5-key"
SSH_HOST="blackowu@server702.web-hosting.com"
REMOTE_PATH="/home/blackowu/public_html"
LOCAL_DB_PHP="./db.php"

echo "════════════════════════════════════════════════════════════════"
echo "🚀 Upload db.php to Production"
echo "════════════════════════════════════════════════════════════════"
echo ""

# Check if SSH key exists
if [ ! -f "$SSH_KEY" ]; then
    echo "❌ SSH key not found: $SSH_KEY"
    echo "   Please verify the key location"
    exit 1
fi

# Check if local db.php exists
if [ ! -f "$LOCAL_DB_PHP" ]; then
    echo "❌ Local db.php not found: $LOCAL_DB_PHP"
    exit 1
fi

# Verify db.php contains new error handling
if ! grep -q "BBX_DB_CONNECTED" "$LOCAL_DB_PHP"; then
    echo "⚠️  WARNING: Local db.php does NOT contain BBX_DB_CONNECTED"
    echo "   Are you sure you want to upload this file?"
    read -p "   Continue? (y/n): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        echo "❌ Upload cancelled"
        exit 1
    fi
fi

echo "📤 Uploading db.php to production..."
echo "   Source: $LOCAL_DB_PHP"
echo "   Target: $SSH_HOST:$REMOTE_PATH/db.php"
echo ""

# Upload file
scp -i "$SSH_KEY" "$LOCAL_DB_PHP" "$SSH_HOST:$REMOTE_PATH/db.php"

if [ $? -eq 0 ]; then
    echo "✅ db.php uploaded successfully"
    echo ""

    # Verify upload
    echo "🔍 Verifying upload..."
    ssh -i "$SSH_KEY" "$SSH_HOST" "grep -q 'BBX_DB_CONNECTED' $REMOTE_PATH/db.php && echo '✅ BBX_DB_CONNECTED found in production db.php' || echo '❌ BBX_DB_CONNECTED NOT found'"

    echo ""
    echo "════════════════════════════════════════════════════════════════"
    echo "✅ UPLOAD COMPLETE"
    echo "════════════════════════════════════════════════════════════════"
    echo ""
    echo "Next steps:"
    echo "  1. Clear Cloudflare cache"
    echo "  2. Test FAQ/Blog pages: https://blackbox.codes/faq.php"
    echo "  3. Check error logs: ssh -i $SSH_KEY $SSH_HOST 'tail -50 $REMOTE_PATH/error_log'"
    echo ""
else
    echo "❌ Upload failed"
    exit 1
fi
