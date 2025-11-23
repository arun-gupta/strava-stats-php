#!/bin/bash

# Quickstart script for Strava Stats PHP application
# This script sets up and runs the application locally

set -e  # Exit on error

echo "🏃 Strava Stats PHP - Quickstart"
echo "=================================="
echo ""

# Check for PHP
if ! command -v php &> /dev/null; then
    echo "❌ PHP is not installed. Please install PHP 8.1 or higher."
    exit 1
fi

PHP_VERSION=$(php -r "echo PHP_VERSION;")
echo "✅ PHP version: $PHP_VERSION"

# Check for Composer
if ! command -v composer &> /dev/null; then
    echo "❌ Composer is not installed."
    echo "   Install it with: brew install composer"
    exit 1
fi

echo "✅ Composer found"

# Check for npm
if ! command -v npm &> /dev/null; then
    echo "❌ npm is not installed. Please install Node.js and npm."
    exit 1
fi

echo "✅ npm found"
echo ""

# Install Composer dependencies
if [ ! -d "vendor" ]; then
    echo "📦 Installing PHP dependencies..."
    composer install
    echo ""
else
    echo "✅ PHP dependencies already installed"
fi

# Install npm dependencies
if [ ! -d "node_modules" ]; then
    echo "📦 Installing JavaScript dependencies..."
    npm install
    echo ""
else
    echo "✅ JavaScript dependencies already installed"
fi

# Build front-end assets
if [ ! -d "public/build" ]; then
    echo "🔨 Building front-end assets..."
    npm run build
    echo ""
else
    echo "✅ Front-end assets already built"
fi

# Check for .env file and prompt configuration
if [ ! -f ".env" ]; then
    echo "⚙️  Creating .env file from .env.example..."
    cp .env.example .env
    echo ""
    echo "⚠️  CONFIGURATION REQUIRED"
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    echo ""
    echo "Before starting the server, you must configure your Strava API credentials:"
    echo ""
    echo "1. Register your app at: https://www.strava.com/settings/api"
    echo "2. Edit the .env file and set:"
    echo "   - STRAVA_CLIENT_ID=your_client_id"
    echo "   - STRAVA_CLIENT_SECRET=your_client_secret"
    echo "   - STRAVA_REDIRECT_URI=http://localhost:8080/auth/callback"
    echo ""
    echo "3. Optionally generate a session secret:"
    echo "   php -r \"echo bin2hex(random_bytes(32));\""
    echo ""
    read -p "Press Enter after configuring .env to continue, or Ctrl+C to exit..."
    echo ""
else
    # Check if .env still has placeholder values
    if grep -q "your_client_id_here" .env || grep -q "your_client_secret_here" .env; then
        echo "⚠️  WARNING: .env file contains placeholder values"
        echo ""
        echo "Please edit .env and configure your Strava API credentials:"
        echo "   - STRAVA_CLIENT_ID"
        echo "   - STRAVA_CLIENT_SECRET"
        echo "   - STRAVA_REDIRECT_URI"
        echo ""
        echo "Register your app at: https://www.strava.com/settings/api"
        echo ""
        read -p "Press Enter after configuring .env to continue, or Ctrl+C to exit..."
        echo ""
    else
        echo "✅ .env file configured"
    fi
fi

# Create necessary directories
mkdir -p logs cache

echo ""
echo "🚀 Starting development server..."
echo "   Access the application at: http://localhost:8080"
echo "   Health check endpoint: http://localhost:8080/healthz"
echo ""
echo "   Press Ctrl+C to stop the server"
echo ""

# Start PHP built-in server
php -S localhost:8080 -t public
