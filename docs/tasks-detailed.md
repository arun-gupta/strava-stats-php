# Detailed Task Breakdown — Strava Activity Analyzer (PHP)

This document provides a granular, modular breakdown of implementation tasks organized into phases and sub-phases.

## Phase 0 — Project Setup & Foundations ✅ COMPLETE

### 0.1 Framework Selection ✅
- [x] Research and compare Slim vs Laravel for project needs
- [x] Document framework decision and rationale in README
- [x] Justify choice based on project requirements

### 0.2 Project Initialization ✅
- [x] Create composer.json with project metadata
- [x] Configure PSR-4 autoloading for App namespace
- [x] Set minimum PHP version requirement (8.1+)
- [x] Add initial Composer dependencies

### 0.3 Directory Structure ✅
- [x] Create src/ with subdirectories (Controllers, Services, Models, Middleware)
- [x] Create public/ directory with index.php entry point
- [x] Create config/ directory for configuration files
- [x] Create routes/ directory for route definitions
- [x] Create views/ directory with layouts and pages
- [x] Create resources/ directory for front-end assets
- [x] Create tests/, logs/, cache/ directories

### 0.4 Core Dependencies ✅
- [x] Add Slim Framework 4 and PSR-7 implementation
- [x] Add Guzzle HTTP client
- [x] Add League OAuth2 client
- [x] Add Carbon for date/time handling
- [x] Add vlucas/phpdotenv for environment configuration
- [x] Add Monolog for logging
- [x] Run composer install

### 0.5 Front-End Setup ✅
- [x] Create package.json with Chart.js dependency
- [x] Configure Vite bundler (vite.config.js)
- [x] Create resources/js/app.js with Chart.js initialization
- [x] Create resources/css/app.css with base styles
- [x] Set up build scripts (npm run dev, npm run build)

### 0.6 Environment Configuration ✅
- [x] Create .env.example template
- [x] Define all required environment variables
- [x] Create .env file for local development
- [x] Document environment variable purposes
- [x] Add .env to .gitignore

### 0.7 Routing System ✅
- [x] Create public/index.php as application entry point
- [x] Set up Slim app initialization
- [x] Configure error middleware
- [x] Create routes/web.php for web routes
- [x] Create routes/api.php for API routes
- [x] Implement route loading mechanism

### 0.8 Logging Configuration ✅
- [x] Create config/logging.php configuration file
- [x] Implement Logger service wrapper
- [x] Configure log levels and destinations
- [x] Set up log file rotation strategy
- [x] Test logging functionality

### 0.9 Health Check Endpoint ✅
- [x] Implement GET /healthz route
- [x] Return JSON with status, timestamp, service name, version
- [x] Test endpoint returns 200 OK
- [x] Document endpoint in API routes

### 0.10 View System & Home Page ✅
- [x] Create View service for rendering templates
- [x] Create views/layouts/main.php layout template
- [x] Create views/pages/home.php welcome page
- [x] Implement home page route (GET /)
- [x] Add basic styling and branding
- [x] Test rendering in browser

### 0.11 Development Tools (Bonus) ✅
- [x] Create quickstart.sh automated setup script
- [x] Add dependency checking (PHP, Composer, npm)
- [x] Add interactive .env configuration prompts
- [x] Add automatic dependency installation
- [x] Add development server startup
- [x] Make script executable

### 0.12 Documentation (Bonus) ✅
- [x] Create docs/INSTALLATION.md with detailed setup
- [x] Add production deployment instructions
- [x] Add Nginx/Apache configuration examples
- [x] Add security checklist
- [x] Add troubleshooting section
- [x] Simplify and improve main README
- [x] Update .gitignore for all artifacts

---

## Phase 1 — Authentication & Security (Requirements 1-2)

### 1.1 OAuth2 Configuration
- [ ] Research Strava OAuth2 flow and requirements
- [ ] Create config/oauth.php configuration file
- [ ] Define OAuth scopes needed (read, activity:read_all)
- [ ] Configure OAuth redirect URI handling
- [ ] Document OAuth app registration process

### 1.2 Session Management
- [ ] Evaluate session storage options (native PHP sessions vs Redis)
- [ ] Implement session configuration
- [ ] Create SessionService for session handling
- [ ] Configure secure session cookies (httpOnly, secure, sameSite)
- [ ] Implement session regeneration for security

### 1.3 OAuth Authorization Flow
- [ ] Create OAuthController
- [ ] Implement GET /auth/strava route
- [ ] Generate OAuth authorization URL with state parameter
- [ ] Implement CSRF protection with state validation
- [ ] Add PKCE support (code_challenge, code_verifier)
- [ ] Redirect user to Strava authorization page
- [ ] Test authorization initiation

### 1.4 OAuth Callback Handler
- [ ] Implement GET /auth/callback route
- [ ] Validate state parameter for CSRF protection
- [ ] Exchange authorization code for access token
- [ ] Handle OAuth errors (access_denied, invalid_grant, etc.)
- [ ] Extract access_token, refresh_token, expires_at
- [ ] Store tokens securely server-side
- [ ] Test callback with mock Strava response

### 1.5 Token Storage
- [ ] Design token storage schema (session or database)
- [ ] Implement TokenService for token management
- [ ] Store access_token, refresh_token, expires_at, athlete_id
- [ ] Ensure tokens never exposed to client-side JavaScript
- [ ] Implement token retrieval methods
- [ ] Add encryption for token storage (optional but recommended)

### 1.6 Token Refresh Logic
- [ ] Implement automatic token refresh on expiry
- [ ] Create RefreshTokenMiddleware
- [ ] Check token expiration before API calls
- [ ] Handle 401 Unauthorized responses with refresh
- [ ] Retry original request after token refresh
- [ ] Handle refresh token expiration/revocation
- [ ] Test refresh flow with expired tokens

### 1.7 Authentication Middleware
- [ ] Create AuthMiddleware to protect routes
- [ ] Check for valid session and tokens
- [ ] Redirect unauthenticated users to home page
- [ ] Apply middleware to protected routes (/dashboard, /api/*)
- [ ] Handle token validation errors gracefully

### 1.8 Sign Out Flow
- [ ] Implement GET /signout route
- [ ] Clear server session
- [ ] Remove stored tokens
- [ ] Redirect to home page
- [ ] Display success message
- [ ] Test complete sign-out flow

### 1.9 User Profile Retrieval
- [ ] Implement GET /api/athlete endpoint in StravaClient
- [ ] Fetch authenticated user's profile on login
- [ ] Store athlete_id, name, profile photo in session
- [ ] Display user info in dashboard header
- [ ] Handle profile fetch errors

### 1.10 Security Hardening
- [ ] Implement rate limiting for OAuth endpoints
- [ ] Add request validation and sanitization
- [ ] Configure HTTPS redirects (production)
- [ ] Set security headers (X-Frame-Options, X-Content-Type-Options)
- [ ] Test CSRF protection
- [ ] Security audit of authentication flow

### 1.11 Error Handling & UX
- [ ] Create error pages for OAuth failures
- [ ] Display user-friendly error messages
- [ ] Implement "Connect with Strava" button on home page
- [ ] Show loading states during OAuth flow
- [ ] Handle edge cases (expired tokens, revoked access)
- [ ] Test all error scenarios

### 1.12 Documentation & Testing
- [ ] Document OAuth setup in README
- [ ] Add authentication flow diagram
- [ ] Write unit tests for token management
- [ ] Write integration tests for OAuth flow
- [ ] Document security considerations
- [ ] Update INSTALLATION.md with OAuth setup

---

## Phase 2 — Strava API Client & Data Layer

### 2.1 HTTP Client Setup
- [ ] Create StravaClient service using Guzzle
- [ ] Configure base URL (https://www.strava.com/api/v3)
- [ ] Implement authentication header injection
- [ ] Configure default timeouts and retry policy
- [ ] Add request/response logging

### 2.2 Rate Limit Handling
- [ ] Research Strava rate limit policies (15-minute and daily limits)
- [ ] Implement rate limit header parsing
- [ ] Create RateLimiter service
- [ ] Implement exponential backoff with jitter
- [ ] Add rate limit tracking and metrics
- [ ] Test rate limit recovery

### 2.3 Activities API - Basic Fetch
- [ ] Implement GET /athlete/activities endpoint
- [ ] Add pagination support (per_page, page parameters)
- [ ] Parse activity response into Activity model
- [ ] Handle empty results
- [ ] Test with sample data

### 2.4 Activities API - Date Filtering
- [ ] Add before/after timestamp parameters
- [ ] Implement date range filtering
- [ ] Handle timezone conversions
- [ ] Test filtering with various date ranges

### 2.5 Activity Data Model
- [ ] Create Activity model class
- [ ] Map Strava API fields to model properties
- [ ] Handle optional fields (laps, splits, segment efforts)
- [ ] Implement type conversions (distance, duration, pace)
- [ ] Add model validation

### 2.6 Pagination & Batch Fetching
- [ ] Implement automatic pagination through all pages
- [ ] Create ActivityRepository for batch operations
- [ ] Add progress tracking for large fetches
- [ ] Implement configurable page size
- [ ] Test with large datasets

### 2.7 Privacy & Scope Handling
- [ ] Check granted OAuth scopes
- [ ] Filter private activities if scope missing
- [ ] Handle visibility field from API
- [ ] Test with different scope combinations
- [ ] Document privacy behavior

### 2.8 Caching Strategy
- [ ] Design cache key structure (user_id + date_range)
- [ ] Implement file-based caching
- [ ] Configure cache TTL (default 5 minutes)
- [ ] Implement cache invalidation
- [ ] Add cache hit/miss metrics
- [ ] Test cache performance

### 2.9 Error Handling
- [ ] Handle network errors and timeouts
- [ ] Handle Strava API errors (4xx, 5xx)
- [ ] Implement retry logic for transient errors
- [ ] Create user-friendly error messages
- [ ] Log API errors for debugging
- [ ] Test error scenarios

### 2.10 API Response Validation
- [ ] Validate API response structure
- [ ] Handle unexpected field types
- [ ] Handle missing required fields
- [ ] Add response schema validation
- [ ] Test with malformed responses

### 2.11 Testing & Mocking
- [ ] Create mock Strava API responses
- [ ] Write unit tests for StravaClient
- [ ] Test pagination logic
- [ ] Test rate limiting
- [ ] Test error handling
- [ ] Create integration tests with mock server

### 2.12 Documentation
- [ ] Document StravaClient API
- [ ] Add code examples for common operations
- [ ] Document rate limit handling
- [ ] Document caching behavior
- [ ] Update developer documentation

---

## Phase 3 — Data Processing & Aggregation

### 3.1 Timezone Normalization
- [ ] Research timezone handling requirements
- [ ] Implement timezone detection (from user profile or browser)
- [ ] Convert all timestamps to user's local timezone
- [ ] Handle DST transitions
- [ ] Test timezone conversions

### 3.2 Activity Type Aggregation
- [ ] Create AggregationService
- [ ] Implement count by activity type
- [ ] Implement duration by activity type
- [ ] Calculate percentages
- [ ] Sort results by count/duration
- [ ] Test with diverse activity types

### 3.3 Date Grouping Utilities
- [ ] Implement daily grouping
- [ ] Implement weekly grouping (ISO week, Mon-Sun)
- [ ] Implement monthly grouping
- [ ] Handle year boundaries
- [ ] Test grouping logic

### 3.4 Running-Specific Aggregation
- [ ] Filter activities by type='Run'
- [ ] Calculate total runs
- [ ] Calculate runs >= 10K
- [ ] Calculate total distance
- [ ] Calculate average pace
- [ ] Handle edge cases (zero runs)

### 3.5 Personal Records (PRs)
- [ ] Implement fastest mile calculation
- [ ] Implement fastest 10K calculation
- [ ] Implement longest run calculation
- [ ] Implement max elevation gain calculation
- [ ] Handle split data if available
- [ ] Test PR calculations

### 3.6 Heatmap Data Generation
- [ ] Create calendar grid structure
- [ ] Aggregate activities by day
- [ ] Calculate intensity buckets (time-based)
- [ ] Calculate intensity buckets (distance-based for runs)
- [ ] Format data for front-end consumption
- [ ] Test heatmap generation

### 3.7 Streak Calculation
- [ ] Implement current streak logic
- [ ] Implement longest streak logic
- [ ] Implement days since last activity
- [ ] Handle ongoing streaks (ending today)
- [ ] Test streak calculations

### 3.8 Gap Analysis
- [ ] Calculate workout days vs missed days
- [ ] Identify gap periods (consecutive days without activity)
- [ ] Calculate longest gap
- [ ] Calculate total gap days
- [ ] Generate gap detail list (start, end, duration)
- [ ] Test gap calculations

### 3.9 Trend Calculations
- [ ] Aggregate distance by day/week/month
- [ ] Aggregate pace by day/week/month
- [ ] Implement moving average smoothing
- [ ] Handle pace inversion (speed to pace conversion)
- [ ] Configure smoothing window
- [ ] Test trend calculations

### 3.10 Distance Histogram
- [ ] Define bin sizes (1 mile or 1 km)
- [ ] Bin running distances
- [ ] Extend bins for outliers (>10 miles)
- [ ] Calculate bin counts
- [ ] Format for charting
- [ ] Test histogram generation

### 3.11 Unit Conversions
- [ ] Create UnitsService
- [ ] Implement meters to miles/km
- [ ] Implement seconds to HH:MM:SS
- [ ] Implement m/s to pace (min/mile, min/km)
- [ ] Implement pace to speed conversions
- [ ] Test all conversions

### 3.12 Testing & Optimization
- [ ] Create test datasets with known outcomes
- [ ] Write unit tests for all aggregations
- [ ] Test edge cases (empty data, single activity)
- [ ] Profile performance with large datasets
- [ ] Optimize slow calculations
- [ ] Document aggregation algorithms

---

## Phase 4 — Dashboard UI & Widgets

*(To be broken down further based on requirements)*

### 4.1 Dashboard Layout
### 4.2 Summary Strip
### 4.3 Tab Navigation
### 4.4 Overview Widget
### 4.5 Duration Widget
### 4.6 Heatmap Widget
### 4.7 Running Stats Widget
### 4.8 Trends Widget
### 4.9 Date Filters
### 4.10 Unit Toggle
### 4.11 Loading States
### 4.12 Empty States

---

## Phase 5 — API Endpoints

*(To be broken down further)*

### 5.1 Summary Endpoint
### 5.2 Distributions Endpoint
### 5.3 Heatmap Endpoint
### 5.4 Trends Endpoint
### 5.5 Running Stats Endpoint
### 5.6 Gaps Endpoint
### 5.7 API Documentation
### 5.8 API Testing

---

## Phase 6 — Testing & Quality Assurance

*(To be broken down further)*

### 6.1 Unit Testing Setup
### 6.2 Integration Testing
### 6.3 API Contract Testing
### 6.4 UI Testing
### 6.5 Performance Testing
### 6.6 Security Testing

---

## Phase 7 — Deployment & Operations

*(To be broken down further)*

### 7.1 Docker Setup
### 7.2 CI/CD Pipeline
### 7.3 Production Configuration
### 7.4 Monitoring & Logging
### 7.5 Backup & Recovery

---

## Notes

- Each sub-phase should be completable in 30-60 minutes
- Sub-phases are designed to be independent where possible
- Dependencies between sub-phases are noted
- Each phase can be merged to main when complete
- Tests should accompany each implementation sub-phase
