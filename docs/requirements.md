# Requirements Document

Last updated: 2025-11-18

## Introduction
This application connects to a user’s Strava account to fetch their activities (runs, rides, swims, strength, etc.) and present interactive analytics via a web dashboard. Key functionality includes: secure OAuth2 authentication; fetching, caching, and normalizing Strava activity data; computing aggregates and metrics (distributions, heatmaps, trends, and personal records); rich date filtering; unit/locale handling; robust error handling; and strong privacy/security aligned with Strava’s policies. The system is read-only and focuses on personal insights for the authenticated user.

## Requirements
The following requirements are grouped by theme but maintain a single sequential numbering. Each includes a User Story and Acceptance Criteria.

### Authentication
1. User connects their Strava account
   - User Story: As a user, I want to connect my Strava account so that the app can analyze my activities.
   - Acceptance Criteria: WHEN I click “Connect with Strava” and complete consent via OAuth2 Authorization Code flow, THEN the system SHALL create a session for me and store access/refresh tokens server-side.

2. Token refresh is automatic
   - User Story: As a user, I want seamless access without reauth so that sessions persist across visits.
   - Acceptance Criteria: WHEN an access token is expired, THEN the system SHALL use the refresh token server-side to obtain a new token without user intervention and continue the request.

3. Minimum scopes are clearly displayed
   - User Story: As a privacy‑conscious user, I want to know requested Strava scopes so that I can make an informed choice.
   - Acceptance Criteria: WHEN the OAuth consent screen is initiated, THEN the system SHALL display the scopes requested (e.g., read, activity:read_all if needed for private activities) and link to an explanation before redirecting.

### Data Acquisition and Normalization
4. Fetch activities for the authenticated user
   - User Story: As a user, I want my activities imported so that the dashboard reflects my training.
   - Acceptance Criteria: WHEN I first sign in, THEN the system SHALL call Strava’s activities API with pagination to retrieve my activities, respecting rate limits.

5. Transparent pagination and caching
   - User Story: As a user, I want fast, complete results so that I can explore my entire history.
   - Acceptance Criteria: WHEN multiple pages of activities exist, THEN the system SHALL fetch all pages transparently and cache results to reduce subsequent API calls.

6. Timezone normalization
   - User Story: As a user, I want accurate daily/weekly rollups so that charts reflect my local time.
   - Acceptance Criteria: WHEN activities are processed, THEN the system SHALL normalize timestamps to the user’s timezone for all aggregations and filters.

7. Privacy respect for private activities
   - User Story: As a user, I want my private activities handled correctly so that my privacy settings are respected.
   - Acceptance Criteria: WHEN private activities are present and permitted by granted scopes, THEN the system SHALL include them in analytics, otherwise it SHALL exclude them.

### Dashboard Analytics and Widgets
8. Activity count distribution
   - User Story: As a user, I want counts per activity type so that I can see my training mix.
   - Acceptance Criteria: WHEN activities are loaded under the active date filter, THEN the system SHALL compute counts and percentages per activity type suitable for a pie/donut chart.

9. Time distribution by activity type
   - User Story: As a user, I want total moving time by activity type so that I can see time allocation.
   - Acceptance Criteria: WHEN activities are loaded, THEN the system SHALL aggregate moving time per type and format as HH:MM for charting.

10. Workout heatmap with streaks
    - User Story: As a user, I want a calendar heatmap of all activities so that I can visualize training consistency.
    - Acceptance Criteria: WHEN the dashboard renders, THEN the system SHALL display a calendar-like grid colored by daily cumulative activity time and SHALL compute current and longest any-activity streaks.

11. Running heatmap with streaks
    - User Story: As a runner, I want a daily running mileage heatmap so that I can track run frequency.
    - Acceptance Criteria: WHEN running activities exist, THEN the system SHALL color daily cells by running distance and compute current and longest running streaks.

12. Running stats (distribution and totals)
    - User Story: As a runner, I want distribution and totals so that I can assess volume and pace.
    - Acceptance Criteria: WHEN running data is available, THEN the system SHALL provide: a histogram of run distances (default 0–10 mi in 1‑mi bins or 0–16 km in 1‑km bins with auto-extend), totals including runs ≥10K, total distance, and average pace (MM:SS per unit).

13. Personal records (PRs)
    - User Story: As a runner, I want key PRs so that I can see my best efforts.
    - Acceptance Criteria: WHEN activities are processed, THEN the system SHALL compute fastest mile, fastest 10K, longest run by distance, and most elevation gain in a run from available activity data.

14. Mileage trend
    - User Story: As a user, I want mileage trends so that I can visualize changes over time.
    - Acceptance Criteria: WHEN activities are loaded, THEN the system SHALL render daily, weekly, and monthly running mileage aggregates with smoothing and tooltips.

15. Pace trend
    - User Story: As a user, I want pace trends so that I can understand fitness progression.
    - Acceptance Criteria: WHEN activities are loaded, THEN the system SHALL render daily, weekly, and monthly average running pace (MM:SS) and correctly handle pace derived from speed.

### Date Filtering and Persistence
16. Preset date ranges
    - User Story: As a user, I want quick presets so that filtering is effortless.
    - Acceptance Criteria: WHEN I open the date filter, THEN the system SHALL provide presets for 7d, 30d, 90d, 6m, 1y, YTD, and All Time.

17. Custom range selection and persistence
    - User Story: As a user, I want custom date ranges to persist so that my context is maintained.
    - Acceptance Criteria: WHEN I select a custom start and end date, THEN the system SHALL recompute all widgets and persist the selection in URL or session so refresh preserves context.

### Internationalization and Units
18. Unit selection and locale-aware formats
    - User Story: As a global user, I want imperial/metric units and locale formats so that data is familiar to me.
    - Acceptance Criteria: WHEN units are configured (auto by locale or explicit), THEN the system SHALL render distances, paces, and dates using the chosen units and locale-appropriate formats.

### Error Handling and Empty States
19. Graceful empty states
    - User Story: As a user, I want helpful empty states so that I understand what to do next.
    - Acceptance Criteria: WHEN no activities exist for the active filter, THEN the system SHALL show an informative message and controls to adjust the date range or retry fetching.

20. Network/API and rate limit errors
    - User Story: As a user, I want clear error messages and retries so that temporary issues don’t block me.
    - Acceptance Criteria: WHEN Strava returns errors or rate limits are hit, THEN the system SHALL back off per limits, surface user‑friendly messages, and provide a retry action without losing context.

21. Token revoked or expired
    - User Story: As a user, I want to easily reauthenticate so that I can continue using the app.
    - Acceptance Criteria: WHEN the refresh or access token is invalid or revoked, THEN the system SHALL prompt re‑authentication and, upon success, resume the prior operation.

### Security and Privacy
22. Secrets and tokens are server-side only
    - User Story: As a security‑minded user, I want my tokens protected so that my account remains safe.
    - Acceptance Criteria: WHEN authentication and API calls occur, THEN the system SHALL never expose client secrets or tokens to the browser and SHALL store tokens securely on the server.

23. CSRF protection
    - User Story: As a user, I want protection from CSRF so that my session can’t be abused.
    - Acceptance Criteria: WHEN handling OAuth callbacks or state‑changing endpoints, THEN the system SHALL validate an unguessable state parameter and reject mismatches.

24. Minimal PII and safe logging
    - User Story: As a privacy‑conscious user, I want minimal data retention so that my personal data isn’t over‑collected.
    - Acceptance Criteria: WHEN storing or logging data, THEN the system SHALL store only what is necessary for functionality and SHALL avoid logging tokens or personal data.

### Session and Sign‑out
25. Sign out terminates access
    - User Story: As a user, I want to sign out so that my session ends on this device.
    - Acceptance Criteria: WHEN I click Sign Out, THEN the system SHALL destroy my session and prevent further API access until I sign in again.

### Performance and Robustness
26. Reasonable initial load
    - User Story: As a user, I want the dashboard to load promptly so that I can start exploring quickly.
    - Acceptance Criteria: WHEN first loading after connect, THEN the system SHALL show a loading state, stream or progressively render widgets as data becomes available, and keep the UI responsive while background pagination completes.

27. Rate limit backoff
    - User Story: As a user, I want reliable fetches so that the app remains usable during heavy use.
    - Acceptance Criteria: WHEN the Strava rate limit is approached or exceeded, THEN the system SHALL apply exponential backoff and schedule retries consistent with Strava policies.

### UI/UX and Accessibility
28. Accessible and responsive UI
    - User Story: As a user, I want a responsive, accessible dashboard so that I can use it on any device.
    - Acceptance Criteria: WHEN using keyboard navigation or screen readers, THEN the system SHALL provide labeled controls, focus order, and sufficient contrast; and SHALL render correctly on mobile and desktop viewports.

29. Error and status messaging
    - User Story: As a user, I want clear status indicators so that I know what the app is doing.
    - Acceptance Criteria: WHEN data is loading, empty, or failed, THEN the system SHALL display non-technical, actionable messages and avoid blocking the entire UI where possible.

### Compliance
30. Strava Terms of Service compliance
    - User Story: As a responsible developer/user, I want compliance so that the app remains permitted.
    - Acceptance Criteria: WHEN interacting with Strava APIs and data, THEN the system SHALL adhere to Strava API Terms of Service and branding guidelines.

# IDEs
.idea/
.vscode/
*.swp
*.swo

# OS
.DS_Store
Thumbs.db

# PHP
vendor/
.env
*.log
