#!/bin/bash

# Dashboard Performance Profiling Script
# This script helps profile the dashboard performance by automatically
# extracting the access token and running the profiler

set -e

echo "╔════════════════════════════════════════════════════════════════════════════╗"
echo "║                     DASHBOARD PERFORMANCE PROFILER                         ║"
echo "╚════════════════════════════════════════════════════════════════════════════╝"
echo ""

# Check if PHP is available
if ! command -v php &> /dev/null; then
    echo "Error: PHP is not installed or not in PATH"
    exit 1
fi

# Get the script directory
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
PROJECT_ROOT="$( cd "$SCRIPT_DIR/.." && pwd )"

# Check if access token is provided as argument
if [ -n "$1" ]; then
    ACCESS_TOKEN="$1"
    echo "Using provided access token"
else
    # Try to read from .env file
    ENV_FILE="$PROJECT_ROOT/.env"
    if [ -f "$ENV_FILE" ] && grep -q "STRAVA_TEST_TOKEN=" "$ENV_FILE"; then
        ACCESS_TOKEN=$(grep "STRAVA_TEST_TOKEN=" "$ENV_FILE" | cut -d '=' -f2 | tr -d '"' | tr -d "'")
        if [ -n "$ACCESS_TOKEN" ]; then
            echo "Using access token from .env file (STRAVA_TEST_TOKEN)"
        fi
    fi
fi

# If still no token, provide instructions
if [ -z "$ACCESS_TOKEN" ]; then
    echo "Error: No access token provided"
    echo ""
    echo "Usage: $0 <access_token>"
    echo ""
    echo "To get your access token:"
    echo "1. Log in to the application at http://localhost:8000"
    echo "2. Open browser developer tools (F12)"
    echo "3. Go to Application > Storage > Session Storage"
    echo "4. Look for 'access_token' key and copy its value"
    echo ""
    echo "Alternatively, add this line to your .env file:"
    echo "STRAVA_TEST_TOKEN=your_access_token_here"
    echo ""
    exit 1
fi

echo ""
echo "Running performance profiler..."
echo ""

# Run the profiler
php "$SCRIPT_DIR/performance_profile.php" "$ACCESS_TOKEN"

echo ""
echo "Profiling complete!"
echo ""
