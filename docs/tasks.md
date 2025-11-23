# Development Tasks — Strava Activity Analyzer (PHP)

**Philosophy:** This task list is organized to prioritize **visible, working features** in the application. Each phase delivers something users can see and interact with, following a vertical slice approach.

---

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
- [x] Auto-open browser when server starts (cross-platform support)
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

## Phase 1 — End-to-End Authentication Flow (Deliver: Working Strava Login)

**Goal:** User can click "Connect with Strava" and successfully authenticate, seeing their name/photo in the app.

### 1.1 OAuth Configuration & "Connect with Strava" Button ✅
- [x] Create config/oauth.php with Strava OAuth settings
- [x] Update home page with styled "Connect with Strava" button
- [x] Create AuthController with placeholder methods
- [x] **VISIBLE:** User sees working button on home page

### 1.2 OAuth Authorization Flow (User → Strava)
- [ ] Implement GET /auth/strava route in AuthController
- [ ] Generate OAuth URL with state parameter and PKCE
- [ ] Store state in session for validation
- [ ] Redirect user to Strava authorization page
- [ ] **VISIBLE:** User is redirected to Strava when clicking button

### 1.3 OAuth Callback & Token Exchange (Strava → App)
- [ ] Implement GET /auth/callback route
- [ ] Validate state parameter (CSRF protection)
- [ ] Exchange authorization code for access token using Guzzle
- [ ] Store tokens in session (access_token, refresh_token, expires_at)
- [ ] Redirect to dashboard on success
- [ ] **VISIBLE:** User returns to app after Strava authorization

### 1.4 User Profile Display
- [ ] Create StravaClient service with getAthlete() method
- [ ] Fetch user profile from Strava API after login
- [ ] Store athlete name and profile photo URL in session
- [ ] Update layout header to show user name and photo when logged in
- [ ] Add "Sign Out" link in header
- [ ] **VISIBLE:** User sees their Strava profile info in the app header

### 1.5 Dashboard Page Scaffold
- [ ] Create views/pages/dashboard.php
- [ ] Implement GET /dashboard route (protected)
- [ ] Show welcome message with user's name
- [ ] Add placeholder sections for future widgets
- [ ] Style dashboard layout
- [ ] **VISIBLE:** User lands on personalized dashboard after login

### 1.6 Sign Out Flow
- [ ] Implement GET /signout route
- [ ] Clear session and stored tokens
- [ ] Redirect to home page with success message
- [ ] Test complete login → logout cycle
- [ ] **VISIBLE:** User can sign out and return to home page

### 1.7 Authentication Middleware & Route Protection
- [ ] Create AuthMiddleware to check for valid session
- [ ] Apply middleware to /dashboard and /api/* routes
- [ ] Redirect unauthenticated users to home page
- [ ] Test protected routes require authentication
- [ ] **VISIBLE:** Unauthenticated users can't access dashboard

### 1.8 Token Refresh (Background)
- [ ] Implement automatic token refresh before expiry
- [ ] Handle 401 responses by refreshing token and retrying
- [ ] Update session with new tokens
- [ ] Log refresh events
- [ ] Test with expired token scenarios

### 1.9 Error Handling & User Feedback
- [ ] Create error page template for OAuth failures
- [ ] Handle "access_denied" from Strava
- [ ] Handle network/API errors with retry option
- [ ] Display friendly error messages to user
- [ ] **VISIBLE:** User sees helpful errors if OAuth fails

### 1.10 Session Security
- [ ] Configure secure session cookies (httpOnly, secure, sameSite)
- [ ] Implement session regeneration after login
- [ ] Set appropriate session timeout
- [ ] Add CSRF token for forms (if needed)
- [ ] Test session security

---

## Phase 2 — First Widget: Activity Count Chart (Deliver: Real Data Visualization)

**Goal:** User sees a working pie chart showing their activity distribution by sport type.

### 2.1 Basic Strava API Client
- [ ] Create StravaClient service
- [ ] Implement getActivities() method with pagination
- [ ] Add authorization header injection
- [ ] Configure timeout and error handling
- [ ] Test fetching activities

### 2.2 Activity Model & Data Parsing
- [ ] Create Activity model class
- [ ] Map Strava API response to Activity objects
- [ ] Parse: id, type, name, start_date, distance, moving_time
- [ ] Handle missing/optional fields
- [ ] Test with sample Strava data

### 2.3 Activity Fetching for Dashboard
- [ ] Fetch user's recent activities (last 30 days by default)
- [ ] Store activities in session or cache
- [ ] Handle pagination to get all activities in range
- [ ] Pass activities to dashboard view
- [ ] Test with real Strava account

### 2.4 Activity Count Aggregation
- [ ] Create AggregationService
- [ ] Implement groupByType() method (count activities by type)
- [ ] Return data structure for charts: [{type, count, percentage}]
- [ ] Handle edge case (no activities)
- [ ] Test aggregation logic

### 2.5 Dashboard: Overview Tab Structure
- [ ] Add tab navigation to dashboard (Overview, Duration, Heatmap, Trends, Running Stats)
- [ ] Create "Overview" tab content area
- [ ] Add canvas element for Chart.js
- [ ] Style tab navigation
- [ ] **VISIBLE:** User sees tab structure in dashboard

### 2.6 Chart.js: Activity Count Pie Chart
- [ ] Pass aggregated data from backend to frontend
- [ ] Create JavaScript module for Overview chart
- [ ] Initialize Chart.js pie chart with activity counts
- [ ] Add labels and colors for different activity types
- [ ] **VISIBLE:** User sees pie chart with their activity distribution

### 2.7 Chart Interactivity & Styling
- [ ] Add tooltips showing count and percentage
- [ ] Add legend below chart
- [ ] Make chart responsive
- [ ] Style chart colors (Strava orange theme)
- [ ] **VISIBLE:** User can hover over chart segments for details

### 2.8 Date Range Filter (Last 30 Days)
- [ ] Add date range display above dashboard ("Showing: Last 30 days")
- [ ] Implement basic date filtering in backend
- [ ] Update chart when date range changes
- [ ] **VISIBLE:** User sees what date range is displayed

### 2.9 Loading States & Empty States
- [ ] Add loading spinner while fetching activities
- [ ] Show "No activities found" message if empty
- [ ] Handle API errors gracefully
- [ ] **VISIBLE:** User sees loading feedback and helpful empty states

### 2.10 Rate Limiting (Basic)
- [ ] Parse Strava rate limit headers
- [ ] Log rate limit usage
- [ ] Add basic exponential backoff on 429 errors
- [ ] Display rate limit message to user if hit
- [ ] Test rate limit handling

---

## Phase 3 — Second Widget: Activity Duration Chart (Deliver: Time Analysis)

**Goal:** User sees a bar chart showing total time spent on each activity type.

### 3.1 Duration Aggregation
- [ ] Add groupByTypeDuration() method to AggregationService
- [ ] Sum moving_time for each activity type
- [ ] Convert seconds to hours for display
- [ ] Return data: [{type, hours, formatted_time}]
- [ ] Test aggregation

### 3.2 Duration Tab & Bar Chart
- [ ] Create "Duration" tab content area
- [ ] Add canvas for bar chart
- [ ] Create JavaScript module for Duration chart
- [ ] Initialize Chart.js bar chart with duration data
- [ ] **VISIBLE:** User sees bar chart with time breakdown by sport

### 3.3 Time Formatting & Labels
- [ ] Format time as "Xh Ym" for labels
- [ ] Add data labels on bars for times > 5% of total
- [ ] Style bar colors consistently with Overview chart
- [ ] **VISIBLE:** User sees clearly formatted time values

### 3.4 Chart Comparison & Insights
- [ ] Show total time across all activities
- [ ] Highlight most time-consuming activity
- [ ] Add simple insight text ("You spent X hours cycling")
- [ ] **VISIBLE:** User sees summary insights above chart

---

## Phase 4 — Third Widget: Training Heatmap (Deliver: Consistency View)

**Goal:** User sees calendar heatmap showing workout consistency, streaks, and gaps.

### 4.1 Calendar Grid Data Generation
- [ ] Create HeatmapService
- [ ] Generate calendar grid for date range
- [ ] Group activities by day
- [ ] Calculate intensity buckets (No Activity, <1h, 1-2h, 2h+)
- [ ] Return grid data with dates and intensities

### 4.2 Heatmap Tab & Canvas Layout
- [ ] Create "Heatmap" tab content area
- [ ] Design horizontal calendar layout (days as rows, weeks as columns)
- [ ] Add legend for intensity levels
- [ ] Add mode toggle (All Activities / Running Only)
- [ ] **VISIBLE:** User sees calendar grid structure

### 4.3 Heatmap Rendering
- [ ] Create JavaScript module for heatmap
- [ ] Render calendar cells with color coding
- [ ] Add tooltips showing date and activity details
- [ ] Make responsive to screen width
- [ ] **VISIBLE:** User sees color-coded calendar heatmap

### 4.4 Streak Calculations
- [ ] Implement current streak calculation
- [ ] Implement longest streak calculation
- [ ] Calculate days since last activity
- [ ] Calculate workout days vs missed days
- [ ] Display streak stats above heatmap
- [ ] **VISIBLE:** User sees their streaks and consistency metrics

### 4.5 Gap Analysis
- [ ] Calculate gap periods (consecutive days without activity)
- [ ] Find longest gap
- [ ] Sum total gap days
- [ ] Add "Show Gap Details" button
- [ ] Display expandable list of gaps with dates
- [ ] **VISIBLE:** User can see detailed gap analysis

### 4.6 Running-Only Mode
- [ ] Filter to only running activities
- [ ] Recalculate heatmap with distance-based intensity buckets
- [ ] Update legend for running mode (<3mi, 3-6mi, 6mi+)
- [ ] Toggle between All Activities and Running Only
- [ ] **VISIBLE:** User can switch between heatmap modes

---

## Phase 5 — Running Stats Widget (Deliver: Runner Insights)

**Goal:** Runners see detailed statistics, PRs, and distance distribution histogram.

### 5.1 Running Stats Tab Scaffold
- [ ] Create "Running Stats" tab content
- [ ] Add sections: Summary, PRs, Distance Distribution
- [ ] Style layout with cards/boxes
- [ ] **VISIBLE:** User sees structured running stats tab

### 5.2 Running Summary Stats
- [ ] Filter activities for type='Run'
- [ ] Calculate: total runs, runs ≥10K, total distance, average pace
- [ ] Format pace as min/mile or min/km
- [ ] Display stats in card grid
- [ ] **VISIBLE:** User sees their running summary

### 5.3 Personal Records (PRs)
- [ ] Calculate fastest mile from activities
- [ ] Calculate fastest 10K
- [ ] Calculate longest run
- [ ] Calculate max elevation gain
- [ ] Display PRs with activity names and dates
- [ ] **VISIBLE:** User sees their personal records

### 5.4 Distance Distribution Histogram
- [ ] Create histogram bins (1-mile or 1-km increments)
- [ ] Bin running distances
- [ ] Extend bins for runs >10 miles
- [ ] Create bar chart for distribution
- [ ] **VISIBLE:** User sees histogram of run distances

### 5.5 Unit Toggle (Metric/Imperial)
- [ ] Add unit toggle button in dashboard header
- [ ] Store preference in session
- [ ] Convert all distances (miles ↔ km)
- [ ] Convert all paces (min/mile ↔ min/km)
- [ ] Update all widgets when toggled
- [ ] **VISIBLE:** User can switch between metric and imperial

---

## Phase 6 — Trends Widget (Deliver: Progress Over Time)

**Goal:** User sees line charts showing mileage and pace trends over time.

### 6.1 Trends Tab & Mode Toggle
- [ ] Create "Trends" tab content
- [ ] Add mode toggle (All Activities / Running Only)
- [ ] Add two chart canvases (Distance, Pace)
- [ ] **VISIBLE:** User sees trends tab structure

### 6.2 Daily/Weekly/Monthly Aggregation
- [ ] Implement daily distance aggregation
- [ ] Implement weekly distance aggregation (ISO week Mon-Sun)
- [ ] Implement monthly distance aggregation
- [ ] Add grain selector (Day/Week/Month)
- [ ] Test aggregation logic

### 6.3 Distance Trend Line Chart
- [ ] Create line chart for distance over time
- [ ] Apply moving average smoothing (configurable window)
- [ ] Format X-axis (dates) and Y-axis (distance)
- [ ] **VISIBLE:** User sees distance trend chart

### 6.4 Pace Trend Line Chart
- [ ] Calculate average pace for each time period
- [ ] Handle pace inversion (speed → pace conversion)
- [ ] Create line chart for pace over time
- [ ] Show pace improving (going down) as positive
- [ ] **VISIBLE:** User sees pace trend chart

### 6.5 Trend Insights
- [ ] Calculate trend direction (improving/declining)
- [ ] Show week-over-week or month-over-month change
- [ ] Display simple insight text
- [ ] **VISIBLE:** User sees trend analysis summary

---

## Phase 7 — Date Range Filters (Deliver: Custom Time Analysis)

**Goal:** User can filter all widgets by custom date ranges.

### 7.1 Date Filter UI
- [ ] Add date range selector above dashboard
- [ ] Create preset buttons (7d, 30d, 90d, YTD, All Time)
- [ ] Add custom date picker (start/end dates)
- [ ] Style filter controls
- [ ] **VISIBLE:** User sees date filter controls

### 7.2 Filter Application
- [ ] Capture date range selection
- [ ] Reload activities for selected range
- [ ] Update all widgets with filtered data
- [ ] Show loading state during refetch
- [ ] **VISIBLE:** All charts update when date range changes

### 7.3 URL/Session Persistence
- [ ] Store date range in URL query params
- [ ] Persist date range in session
- [ ] Restore on page refresh
- [ ] Default to "Last 7 days" on first load
- [ ] **VISIBLE:** User's filter selection persists

### 7.4 Performance Optimization
- [ ] Implement caching for common date ranges
- [ ] Add cache invalidation strategy
- [ ] Test with large datasets (5000+ activities)
- [ ] Optimize to meet <2s load time target
- [ ] **VISIBLE:** Dashboard loads quickly even with large datasets

---

## Phase 8 — Polish & Production Ready

**Goal:** Application is production-ready with robust error handling, testing, and deployment.

### 8.1 Comprehensive Error Handling
- [ ] Handle all Strava API error codes
- [ ] Implement retry logic with exponential backoff
- [ ] Display user-friendly error messages
- [ ] Log errors for debugging
- [ ] Test error scenarios

### 8.2 Unit Testing
- [ ] Write tests for Activity model
- [ ] Write tests for AggregationService
- [ ] Write tests for HeatmapService
- [ ] Write tests for UnitsService
- [ ] Achieve >80% code coverage

### 8.3 Integration Testing
- [ ] Test OAuth flow end-to-end
- [ ] Test activity fetching with mock API
- [ ] Test dashboard rendering
- [ ] Test all widgets
- [ ] Test rate limiting

### 8.4 Security Hardening
- [ ] Security audit of authentication flow
- [ ] Enable HTTPS enforcement (production)
- [ ] Set security headers
- [ ] Validate all inputs
- [ ] Test CSRF protection

### 8.5 Performance Profiling
- [ ] Profile dashboard load time
- [ ] Optimize slow queries
- [ ] Optimize front-end bundle size
- [ ] Meet <2s load time and <300ms filter update targets
- [ ] Document performance metrics

### 8.6 Documentation
- [ ] Complete API documentation
- [ ] Document all configuration options
- [ ] Add troubleshooting guide
- [ ] Create deployment guide
- [ ] Update README with screenshots

### 8.7 Deployment
- [ ] Create Dockerfile
- [ ] Set up CI/CD pipeline
- [ ] Deploy to staging environment
- [ ] Smoke test in staging
- [ ] Deploy to production

---

## Notes

- **Each phase delivers a working, visible feature**
- Sub-phases within each phase build progressively toward the deliverable
- Phases can be demoed to stakeholders as they complete
- Testing happens alongside feature development, not as a separate phase
- Focus on "make it work, make it visible, then make it better"
