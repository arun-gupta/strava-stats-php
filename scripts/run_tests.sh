#!/bin/bash

# Test Runner Script
# Convenient wrapper for running PHPUnit tests with various options

set -e

SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
PROJECT_ROOT="$( cd "$SCRIPT_DIR/.." && pwd )"

cd "$PROJECT_ROOT"

echo "╔════════════════════════════════════════════════════════════════════════════╗"
echo "║                         STRAVA ANALYZER TEST SUITE                         ║"
echo "╚════════════════════════════════════════════════════════════════════════════╝"
echo ""

# Check if PHPUnit is available
if [ ! -f "./vendor/bin/phpunit" ]; then
    echo "Error: PHPUnit not found. Run 'composer install' first."
    exit 1
fi

# Parse arguments
COVERAGE=false
TESTDOX=true
FILTER=""
SUITE=""

while [[ $# -gt 0 ]]; do
    case $1 in
        --coverage)
            COVERAGE=true
            shift
            ;;
        --no-testdox)
            TESTDOX=false
            shift
            ;;
        --filter)
            FILTER="$2"
            shift 2
            ;;
        --suite)
            SUITE="$2"
            shift 2
            ;;
        --help)
            echo "Usage: $0 [OPTIONS]"
            echo ""
            echo "Options:"
            echo "  --coverage       Generate code coverage report (requires pcov or xdebug)"
            echo "  --no-testdox     Disable testdox output format"
            echo "  --filter NAME    Run only tests matching NAME"
            echo "  --suite NAME     Run specific test suite (Unit or Integration)"
            echo "  --help           Show this help message"
            echo ""
            echo "Examples:"
            echo "  $0                          # Run all tests with testdox"
            echo "  $0 --coverage              # Run tests with coverage report"
            echo "  $0 --filter Activity       # Run only Activity tests"
            echo "  $0 --suite Unit            # Run only unit tests"
            exit 0
            ;;
        *)
            echo "Unknown option: $1"
            echo "Use --help for usage information"
            exit 1
            ;;
    esac
done

# Build PHPUnit command
PHPUNIT_CMD="./vendor/bin/phpunit"

if [ "$TESTDOX" = true ]; then
    PHPUNIT_CMD="$PHPUNIT_CMD --testdox"
fi

if [ -n "$FILTER" ]; then
    PHPUNIT_CMD="$PHPUNIT_CMD --filter $FILTER"
    echo "Filter: $FILTER"
fi

if [ -n "$SUITE" ]; then
    PHPUNIT_CMD="$PHPUNIT_CMD --testsuite $SUITE"
    echo "Suite: $SUITE"
fi

if [ "$COVERAGE" = true ]; then
    # Check if coverage driver is available
    if ! php -m | grep -qE '(pcov|xdebug)'; then
        echo "Warning: No code coverage driver found (pcov or xdebug)"
        echo "Install pcov: pecl install pcov"
        echo "Or install xdebug: pecl install xdebug"
        echo ""
        echo "Running tests without coverage..."
    else
        echo "Generating coverage report..."
        PHPUNIT_CMD="$PHPUNIT_CMD --coverage-html coverage/html --coverage-text"
        echo ""
    fi
fi

echo "Running tests..."
echo "Command: $PHPUNIT_CMD"
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# Run tests
$PHPUNIT_CMD

EXIT_CODE=$?

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

if [ $EXIT_CODE -eq 0 ]; then
    echo "✓ All tests passed!"
else
    echo "✗ Some tests failed (exit code: $EXIT_CODE)"
fi

if [ "$COVERAGE" = true ] && [ -f "coverage/html/index.html" ]; then
    echo ""
    echo "Coverage report generated: coverage/html/index.html"
    echo "Open with: open coverage/html/index.html"
fi

echo ""

exit $EXIT_CODE
