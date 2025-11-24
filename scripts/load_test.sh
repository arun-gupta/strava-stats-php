#!/bin/bash

# Load Testing Script for Strava Activity Analyzer
# Tests dashboard performance under various load conditions

set -e

echo "╔════════════════════════════════════════════════════════════════════════════╗"
echo "║                         DASHBOARD LOAD TESTER                              ║"
echo "╚════════════════════════════════════════════════════════════════════════════╝"
echo ""

# Check if Apache Bench is available
if ! command -v ab &> /dev/null; then
    echo "Error: Apache Bench (ab) is not installed"
    echo ""
    echo "Install on macOS: brew install httpd"
    echo "Install on Ubuntu: sudo apt-get install apache2-utils"
    echo "Install on CentOS: sudo yum install httpd-tools"
    echo ""
    exit 1
fi

# Configuration
HOST="http://localhost:8000"
SESSION_ID=""

# Parse arguments
while [[ $# -gt 0 ]]; do
    case $1 in
        --host)
            HOST="$2"
            shift 2
            ;;
        --session)
            SESSION_ID="$2"
            shift 2
            ;;
        *)
            echo "Unknown option: $1"
            echo "Usage: $0 [--host HOST] [--session SESSION_ID]"
            exit 1
            ;;
    esac
done

echo "Configuration:"
echo "  Host: $HOST"
echo "  Session ID: ${SESSION_ID:-<not provided>}"
echo ""

# Test public endpoints (no authentication needed)
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "TEST 1: Home Page Load"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
ab -n 100 -c 10 -q "$HOST/"
echo ""

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "TEST 2: Health Check Endpoint"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
ab -n 500 -c 50 -q "$HOST/healthz"
echo ""

# Test authenticated endpoints if session provided
if [ -n "$SESSION_ID" ]; then
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo "TEST 3: Dashboard Load (Authenticated)"
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    ab -n 50 -c 5 -q -C "PHPSESSID=$SESSION_ID" "$HOST/dashboard"
    echo ""

    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo "TEST 4: Dashboard with Different Date Ranges"
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

    echo "  Testing: 7 days filter"
    ab -n 20 -c 5 -q -C "PHPSESSID=$SESSION_ID" "$HOST/dashboard?days=7"

    echo "  Testing: 30 days filter"
    ab -n 20 -c 5 -q -C "PHPSESSID=$SESSION_ID" "$HOST/dashboard?days=30"

    echo "  Testing: 90 days filter"
    ab -n 20 -c 5 -q -C "PHPSESSID=$SESSION_ID" "$HOST/dashboard?days=90"
    echo ""

    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo "TEST 5: Stress Test - High Concurrency"
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    ab -n 100 -c 20 -q -C "PHPSESSID=$SESSION_ID" "$HOST/dashboard"
    echo ""
else
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo "Skipping authenticated endpoint tests (no session ID provided)"
    echo ""
    echo "To test authenticated endpoints:"
    echo "1. Log in to the application"
    echo "2. Open browser developer tools"
    echo "3. Go to Application > Cookies"
    echo "4. Copy the PHPSESSID value"
    echo "5. Run: $0 --session <PHPSESSID>"
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo ""
fi

echo "╔════════════════════════════════════════════════════════════════════════════╗"
echo "║                          LOAD TESTING COMPLETE                             ║"
echo "╚════════════════════════════════════════════════════════════════════════════╝"
echo ""
echo "Summary:"
echo "  ✓ Public endpoints tested"
if [ -n "$SESSION_ID" ]; then
    echo "  ✓ Authenticated endpoints tested"
    echo "  ✓ Multiple date ranges tested"
    echo "  ✓ Stress test completed"
else
    echo "  ⚠ Authenticated endpoints skipped (provide --session flag)"
fi
echo ""
echo "Performance targets:"
echo "  • Home page: < 200ms"
echo "  • Dashboard (cold): < 2000ms"
echo "  • Dashboard (cached): < 300ms"
echo "  • Success rate: > 95%"
echo ""
