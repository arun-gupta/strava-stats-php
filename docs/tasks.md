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

### 1.2 OAuth Authorization Flow (User → Strava) ✅
- [x] Implement GET /auth/strava route in AuthController
- [x] Generate OAuth URL with state parameter and PKCE
- [x] Store state in session for validation
- [x] Redirect user to Strava authorization page
- [x] **VISIBLE:** User is redirected to Strava when clicking button

### 1.3 OAuth Callback & Token Exchange (Strava → App) ✅
- [x] Implement GET /auth/callback route
- [x] Validate state parameter (CSRF protection)
- [x] Exchange authorization code for access token using Guzzle
- [x] Store tokens in session (access_token, refresh_token, expires_at)
- [x] Redirect to dashboard on success
- [x] **VISIBLE:** User returns to app after Strava authorization

### 1.4 User Profile Display ✅
- [x] Create StravaClient service with getAthlete() method
- [x] Fetch user profile from Strava API after login
- [x] Store athlete name and profile photo URL in session
- [x] Update layout header to show user name and photo when logged in
- [x] Add "Sign Out" link in header
- [x] **VISIBLE:** User sees their Strava profile info in the app header

### 1.5 Dashboard Page Scaffold ✅
- [x] Create views/pages/dashboard.php
- [x] Implement GET /dashboard route (protected)
- [x] Show welcome message with user's name
- [x] Add placeholder sections for future widgets
- [x] Style dashboard layout
- [x] **VISIBLE:** User lands on personalized dashboard after login

### 1.6 Sign Out Flow ✅
- [x] Implement GET /signout route
- [x] Clear session and stored tokens
- [x] Redirect to home page with success message
- [x] Test complete login → logout cycle
- [x] **VISIBLE:** User can sign out and return to home page

### 1.7 Authentication Middleware & Route Protection ✅
- [x] Create AuthMiddleware to check for valid session
- [x] Apply middleware to /dashboard and /api/* routes
- [x] Redirect unauthenticated users to home page
- [x] Test protected routes require authentication
- [x] **VISIBLE:** Unauthenticated users can't access dashboard

### 1.8 Token Refresh (Background) ✅
- [x] Implement automatic token refresh before expiry
- [x] Handle 401 responses by refreshing token and retrying
- [x] Update session with new tokens
- [x] Log refresh events
- [x] Test with expired token scenarios
- [x] Handle 429 rate limiting with Retry-After header
- [x] Handle 403 forbidden (scope permission issues)
- [x] Handle 404 not found (no retry)
- [x] Handle 5xx server errors with exponential backoff
- [x] Implement max retry limit (3 attempts)

### 1.9 Error Handling & User Feedback ✅
- [x] Create error page template for OAuth failures
- [x] Handle "access_denied" from Strava
- [x] Handle network/API errors with retry option
- [x] Display friendly error messages to user
- [x] **VISIBLE:** User sees helpful errors if OAuth fails

### 1.10 Session Security ✅
- [x] Configure secure session cookies (httpOnly, secure, sameSite)
- [x] Implement session regeneration after login
- [x] Set appropriate session timeout
- [x] Add CSRF token for forms (using state parameter for OAuth)
- [x] Test session security

---

## Phase 2 — First Widget: Activity Count Chart (Deliver: Real Data Visualization)

**Goal:** User sees a working pie chart showing their activity distribution by sport type.

### 2.1 Basic Strava API Client ✅
- [x] Create StravaClient service
- [x] Implement getActivities() method with pagination
- [x] Add authorization header injection
- [x] Configure timeout and error handling
- [x] Test fetching activities

### 2.2 Activity Model & Data Parsing ✅
- [x] Create Activity model class
- [x] Map Strava API response to Activity objects
- [x] Parse: id, type, name, start_date, distance, moving_time
- [x] Handle missing/optional fields
- [x] Test with sample Strava data

### 2.3 Activity Fetching for Dashboard ✅
- [x] Fetch user's recent activities (last 7 days by default)
- [x] Display activity counts by type on dashboard
- [x] Handle pagination to get all activities in range
- [x] Pass activities to dashboard view
- [x] Test with real Strava account
- [x] **VISIBLE:** User sees activity count breakdown by type

### 2.4 Simple Pie Chart with Chart.js ✅
- [x] Add Chart.js library to project (via CDN or npm)
- [x] Add canvas element for pie chart to dashboard
- [x] Pass activity count data to JavaScript
- [x] Create simple pie chart showing activity distribution
- [x] **VISIBLE:** User sees a basic pie chart of their activities

### 2.5 Chart Styling & Polish ✅
- [x] Add Strava-themed colors to chart
- [x] Add tooltips showing count and percentage
- [x] Add responsive sizing
- [x] Add legend
- [x] Add data labels showing count and percentage on slices
- [x] **VISIBLE:** User sees a polished, interactive chart

### 2.6 Date Range Display ✅
- [x] Add date range indicator above chart ("Last 7 days")
- [x] Show total activities count prominently
- [x] **VISIBLE:** User knows what time period they're viewing

### 2.7 Empty State Improvements ✅
- [x] Improve "no activities" messaging
- [x] Add call-to-action to log activities
- [x] Show example chart when empty
- [x] **VISIBLE:** User sees helpful guidance when no data

### 2.8 Loading State ✅
- [x] Add loading spinner while fetching
- [x] Show skeleton/placeholder for chart
- [x] Handle slow API responses gracefully
- [x] **VISIBLE:** User sees loading feedback

### 2.9 Error Handling UI ✅
- [x] Show user-friendly error messages on API failures
- [x] Add retry button for failed requests
- [x] Handle rate limiting visibly (handled in backend with retry logic)
- [x] **VISIBLE:** User understands when something goes wrong

### 2.10 Chart Completion Polish ✅
- [x] Test on different screen sizes (responsive design with max-width)
- [x] Verify all activity types display correctly (supports 8+ colors)
- [x] Add chart title and description (title above chart, labels on slices)
- [x] Ensure accessibility (semantic HTML, ARIA labels via Chart.js)
- [x] **VISIBLE:** Chart works perfectly for all users

---

## Phase 3 — Second Widget: Activity Duration Chart (Deliver: Time Analysis)

**Goal:** User sees a bar chart showing total time spent on each activity type.

### 3.1 Add Tabbed Navigation ✅
- [x] Create tab navigation component (Overview, Duration)
- [x] Make Overview tab active by default (shows current pie chart)
- [x] Add empty Duration tab ready for content
- [x] Style tabs with Strava theme
- [x] **VISIBLE:** User sees organized tab navigation

### 3.2 Display Duration Data as Text First ✅
- [x] Calculate total moving time by activity type
- [x] Display time in "Xh Ym" format in Duration tab
- [x] Show total time across all activities
- [x] **VISIBLE:** User sees time spent on each activity type

### 3.3 Add Duration Bar Chart ✅
- [x] Add canvas for bar chart in Duration section
- [x] Pass duration data to JavaScript
- [x] Create simple Chart.js bar chart
- [x] **VISIBLE:** User sees bar chart with time breakdown

### 3.4 Style & Polish Duration Chart ✅
- [x] Style bars with Strava colors
- [x] Add tooltips with formatted times
- [x] Make responsive
- [x] Add chart title and axis labels
- [x] **VISIBLE:** User sees polished, readable time chart

### 3.5 Add Insights Summary
- [ ] Highlight longest duration activity
- [ ] Show weekly average time
- [ ] Add insight text ("Most active on Tuesdays")
- [ ] **VISIBLE:** User gets actionable insights from data

---

## Phase 4 — Third Widget: Training Heatmap (Deliver: Consistency View)

**Goal:** User sees calendar heatmap showing workout consistency, streaks, and gaps.

### 4.1 Show Basic Streak Stats First
- [ ] Calculate and display current streak (consecutive days with activity)
- [ ] Show longest streak achieved
- [ ] Display total active days vs rest days
- [ ] **VISIBLE:** User sees their streak statistics

### 4.2 Simple Calendar Grid (Text-Based)
- [ ] Group activities by date
- [ ] Display simple text calendar showing days with activities
- [ ] Mark active days with ✓ or color
- [ ] **VISIBLE:** User sees which days they were active

### 4.3 Visual Heatmap with Colors
- [ ] Create intensity buckets (no activity, light, medium, heavy)
- [ ] Render calendar grid with color-coded cells
- [ ] Add legend explaining colors
- [ ] **VISIBLE:** User sees color-coded activity heatmap

### 4.4 Add Tooltips & Interactivity
- [ ] Add hover tooltips showing activity details per day
- [ ] Make cells clickable to show activity list
- [ ] Add smooth animations
- [ ] **VISIBLE:** User can interact with calendar to see details

### 4.5 Gap Analysis Display
- [ ] Show "Days since last activity"
- [ ] Highlight longest gap period in calendar
- [ ] Add "Show Gap Details" expandable section
- [ ] List all gap periods with dates
- [ ] **VISIBLE:** User sees their rest periods and gaps

### 4.6 Running-Only Mode Toggle
- [ ] Add toggle switch for "All Activities" vs "Running Only"
- [ ] Recalculate heatmap for running only
- [ ] Update intensity buckets based on distance
- [ ] **VISIBLE:** User can filter heatmap to running activities

---

## Phase 5 — Running Stats Widget (Deliver: Runner Insights)

**Goal:** Runners see detailed statistics, PRs, and distance distribution histogram.

### 5.1 Show Basic Running Stats First
- [ ] Filter running activities only
- [ ] Display: total runs, total distance, average pace
- [ ] Show in simple text format with cards
- [ ] **VISIBLE:** User sees their running summary stats

### 5.2 Add Personal Records (PRs)
- [ ] Calculate fastest pace from all runs
- [ ] Calculate longest run distance
- [ ] Show runs over 10K count
- [ ] Display PRs with dates
- [ ] **VISIBLE:** User sees their personal records

### 5.3 Simple Distance Distribution List
- [ ] Group runs by distance ranges (0-5K, 5-10K, 10K+)
- [ ] Show count in each range
- [ ] Display as simple list or cards
- [ ] **VISIBLE:** User sees distance distribution

### 5.4 Distance Distribution Histogram
- [ ] Create histogram with Chart.js
- [ ] Use 1-mile or 1-km bins
- [ ] Style bars consistently
- [ ] **VISIBLE:** User sees visual histogram of run distances

### 5.5 Add Unit Toggle (Miles/Kilometers)
- [ ] Add toggle button in header
- [ ] Convert distances and paces
- [ ] Update all displayed values
- [ ] **VISIBLE:** User can switch between units

---

## Phase 6 — Trends Widget (Deliver: Progress Over Time)

**Goal:** User sees line charts showing mileage and pace trends over time.

### 6.1 Show Weekly Summary First
- [ ] Group activities by week
- [ ] Display weekly distance totals in simple list
- [ ] Show current week vs previous week
- [ ] **VISIBLE:** User sees weekly activity summary

### 6.2 Simple Distance Trend Chart
- [ ] Create basic line chart for weekly distance
- [ ] Plot last 4-8 weeks
- [ ] Label axes clearly
- [ ] **VISIBLE:** User sees distance trend over time

### 6.3 Add Pace Trend Chart
- [ ] Calculate average pace per week (running only)
- [ ] Create separate line chart for pace
- [ ] Show pace improving as downward trend
- [ ] **VISIBLE:** User sees pace improvement over time

### 6.4 Add Time Period Selector
- [ ] Add buttons for "Last 4 weeks" / "Last 8 weeks" / "Last 12 weeks"
- [ ] Update charts when period changes
- [ ] **VISIBLE:** User can choose time range

### 6.5 Trend Insights & Smoothing
- [ ] Apply moving average to smooth trends
- [ ] Calculate trend direction (up/down/flat)
- [ ] Show insight text ("Distance increasing by 10%")
- [ ] **VISIBLE:** User sees trend analysis with insights

---

## Phase 7 — Date Range Filters (Deliver: Custom Time Analysis)

**Goal:** User can filter all widgets by custom date ranges.

### 7.1 Add Quick Filter Buttons
- [ ] Add preset buttons: "7 days" / "30 days" / "90 days"
- [ ] Show currently selected period
- [ ] Place buttons prominently above charts
- [ ] **VISIBLE:** User sees and can click filter options

### 7.2 Apply Filters to Dashboard
- [ ] Refetch activities when filter changes
- [ ] Update all visible charts and stats
- [ ] Show loading indicator during refetch
- [ ] **VISIBLE:** Dashboard updates when period changes

### 7.3 Add Custom Date Picker
- [ ] Add "Custom" button to open date picker
- [ ] Use simple date inputs (start/end)
- [ ] Apply custom range to dashboard
- [ ] **VISIBLE:** User can select any date range

### 7.4 Persist User's Selection
- [ ] Store selected range in session
- [ ] Restore on page refresh
- [ ] Show selected range in UI
- [ ] **VISIBLE:** User's filter preference persists

### 7.5 Optimize for Large Datasets
- [ ] Cache fetched activities in session
- [ ] Only refetch if range changes
- [ ] Test with 1000+ activities
- [ ] **VISIBLE:** Dashboard remains fast with lots of data

---

## Phase 8 — Polish & Production Ready

**Goal:** Application is production-ready with polish, performance, and deployment.

### 8.1 Visual Polish Pass
- [ ] Review and improve all styling
- [ ] Ensure consistent spacing and alignment
- [ ] Test on mobile devices
- [ ] Add smooth transitions and animations
- [ ] **VISIBLE:** App looks professional and polished

### 8.2 Performance Optimization
- [ ] Minimize JavaScript bundle size
- [ ] Optimize API calls (reduce requests)
- [ ] Add caching where appropriate
- [ ] Test load times and optimize
- [ ] **VISIBLE:** App loads and responds quickly

### 8.3 Error Handling Polish
- [ ] Review all error messages for clarity
- [ ] Add helpful recovery actions
- [ ] Test all error scenarios
- [ ] **VISIBLE:** Errors are clear and actionable

### 8.4 Accessibility Review
- [ ] Add ARIA labels where needed
- [ ] Test keyboard navigation
- [ ] Ensure color contrast meets standards
- [ ] Test with screen reader
- [ ] **VISIBLE:** App is accessible to all users

### 8.5 Documentation & Deployment
- [ ] Update README with features and screenshots
- [ ] Document deployment process
- [ ] Test production deployment
- [ ] **VISIBLE:** App is deployed and documented
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
