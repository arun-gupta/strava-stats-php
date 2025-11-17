# Requirements Document

Last updated: 2025-11-17

## Introduction

Strava Activity Analyzer is a web application that connects to a user’s Strava account and provides interactive analytics and visualizations for their activities (runs, rides, swims, etc.). The app is read‑only and focuses on secure authentication, reliable data acquisition, robust analytics, and accessible, responsive charts. Out of scope: building a native mobile app, mutating Strava data, and social/leaderboard features.

Notes:
- “User” below refers to the authenticated athlete. Admin/operator tasks are limited to configuration and deployment.
- Requirements are grouped and numbered. Each item includes a User Story and testable Acceptance Criteria.

## 1. Authentication & Security

R1. OAuth2 Authorization Code
> User Story: As a user, I want to sign in with Strava so that I can see my private analytics securely.
> Acceptance Criteria: WHEN I click “Connect with Strava” THEN the system SHALL start the OAuth2 Authorization Code flow and redirect me to Strava’s consent screen.

R2. Token Storage & Refresh
> User Story: As a user, I want my session to persist so that I don’t have to log in frequently.
> Acceptance Criteria: WHEN an access token is near expiry or a 401 is received THEN the system SHALL refresh the token automatically using the refresh token without user intervention.

R3. Scope Disclosure
> User Story: As a user, I want to know what data is requested so that I can make an informed consent decision.
> Acceptance Criteria: WHEN the consent screen is shown THEN the system SHALL clearly list read‑only scopes (e.g., read, activity:read_all if chosen).

R4. Configurable Redirect URIs
> User Story: As an operator, I want configurable callback URLs so that I can run the app in dev, staging, and prod.
> Acceptance Criteria: WHEN the app starts THEN the system SHALL use environment variables to set OAuth redirect URIs appropriate to the environment.

R5. Secrets Not Exposed Client‑Side
> User Story: As an operator, I want secrets kept server‑side so that the system is secure.
> Acceptance Criteria: WHEN building the frontend THEN the system SHALL not embed client secrets; token exchange occurs server‑side.

R6. CSRF & OAuth State
> User Story: As a user, I want my session protected so that attacks are mitigated.
> Acceptance Criteria: WHEN initiating OAuth THEN the system SHALL send and validate a cryptographically strong state value and use secure cookies/CSRF protections for state‑changing endpoints.

## 2. Data Acquisition & Reliability

R7. Fetch Activities (Paginated)
> User Story: As a user, I want my activities fetched so that I can see complete analytics.
> Acceptance Criteria: WHEN authenticated THEN the system SHALL retrieve all activities for the user via Strava’s APIs with pagination until all pages in range are fetched.

R8. Rate Limits & Backoff
> User Story: As a user, I want graceful handling of Strava limits so that the app remains usable.
> Acceptance Criteria: WHEN rate limits are hit THEN the system SHALL back off per policy, surface a friendly message, and retry according to configured limits without crashing.

R9. Caching
> User Story: As a user, I want fast dashboards so that I can iterate on filters quickly.
> Acceptance Criteria: WHEN a dashboard query repeats within the cache TTL THEN the system SHALL serve results from cache and refresh in the background as configured.

R10. Timezone Normalization
> User Story: As a user, I want correct daily/weekly totals so that charts are accurate for my locale.
> Acceptance Criteria: WHEN aggregating by day/week/month THEN the system SHALL normalize timestamps to the user’s timezone consistently.

R11. Privacy Respect
> User Story: As a user, I want control over private activities so that my data remains confidential.
> Acceptance Criteria: WHEN required scopes are absent THEN the system SHALL exclude private activities; WHEN scopes include private THEN they SHALL be included in analytics.

## 3. Analytics & Widgets (recomputed under active date filter)

R12. Activity Count Distribution
> User Story: As a user, I want a breakdown by activity type so that I can understand my training mix.
> Acceptance Criteria: WHEN the dashboard loads THEN the system SHALL compute counts and percentages per activity type for charting.

R13. Time Distribution
> User Story: As a user, I want total moving time by activity type so that I can see time allocation.
> Acceptance Criteria: WHEN the dashboard loads THEN the system SHALL sum moving time by type and format as HH:MM for charting.

R14. Workout Heatmap + Streaks
> User Story: As a user, I want a calendar heatmap so that I can visualize workout frequency and streaks.
> Acceptance Criteria: WHEN activities exist THEN the system SHALL render a date grid with intensity by count or time and compute current and longest streak of any‑activity days.

R15. Running Heatmap + Streaks
> User Story: As a runner, I want daily run mileage intensity so that I can monitor consistency.
> Acceptance Criteria: WHEN runs exist THEN the system SHALL render a date grid with intensity by run distance and compute current and longest running streak.

R16. Running Totals
> User Story: As a runner, I want totals and averages so that I can track performance.
> Acceptance Criteria: WHEN runs exist in range THEN the system SHALL report total runs, runs ≥10K, total distance, and average pace (MM:SS per unit).

R17. Running Distance Histogram
> User Story: As a runner, I want a distribution of run distances so that I can see typical training volumes.
> Acceptance Criteria: WHEN runs exist THEN the system SHALL compute histogram bins (default 1‑mile up to 10 miles, configurable; metric equivalent) and extend if data exceeds.

R18. Personal Records (PRs)
> User Story: As a runner, I want to see my PRs so that I can benchmark progress.
> Acceptance Criteria: WHEN computing PRs THEN the system SHALL report fastest mile, fastest 10K, longest run, and most elevation in a run; prefer split/segment data if available, else approximate from average speed.

R19. Mileage Trend
> User Story: As a runner, I want daily/weekly/monthly mileage trends so that I can analyze training load.
> Acceptance Criteria: WHEN runs exist THEN the system SHALL compute aggregates for daily, weekly (documented rule), and monthly periods with optional smoothing.

R20. Pace Trend
> User Story: As a runner, I want pace trends so that I can track speed changes over time.
> Acceptance Criteria: WHEN runs exist THEN the system SHALL compute average pace over selected periods and handle speed→pace inversion correctly.

## 4. Date Filters & Persistence

R21. Preset Ranges
> User Story: As a user, I want preset ranges so that I can filter quickly.
> Acceptance Criteria: WHEN I choose 7d, 30d, 90d, 6m, 1y, YTD, or All Time THEN all widgets SHALL recompute using the selection.

R22. Custom Range + Persistence
> User Story: As a user, I want custom dates persisted so that refreshes keep context.
> Acceptance Criteria: WHEN I set a custom start/end THEN the system SHALL persist selection in URL or session and restore it on reload.

## 5. Internationalization & Units

R23. Units Toggle
> User Story: As a user, I want to switch units so that values match my preference.
> Acceptance Criteria: WHEN I toggle imperial/metric THEN all numeric values, axes, and labels SHALL update consistently.

R24. Locale Formatting
> User Story: As a user, I want dates and numbers formatted for my locale so that the UI is clear.
> Acceptance Criteria: WHEN rendering dates/numbers THEN the system SHALL apply locale‑appropriate formats where possible.

## 6. Error Handling & Empty States

R25. No‑Data States
> User Story: As a user, I want graceful empty states so that I understand what to do next.
> Acceptance Criteria: WHEN no activities fall within the active range THEN the system SHALL show informative empty states without errors.

R26. API/Network Failures
> User Story: As a user, I want clear recoverable errors so that I can retry.
> Acceptance Criteria: WHEN Strava/API requests fail THEN the system SHALL show helpful messages and provide retry controls.

R27. Rate Limit Exceeded UI
> User Story: As a user, I want to know when limits are hit so that I can wait or try later.
> Acceptance Criteria: WHEN rate limits are exceeded THEN the system SHALL present a friendly notice including approximate retry timing when known.

R28. Token Revoked/Expired UX
> User Story: As a user, I want to re‑authenticate seamlessly so that I’m not blocked.
> Acceptance Criteria: WHEN a refresh fails or token is invalid THEN the system SHALL prompt re‑auth and preserve intended navigation after success.

## 7. Performance, Accessibility, Observability

R29. Performance ≤2s (Cached)
> User Story: As a user, I want fast dashboards so that I stay engaged.
> Acceptance Criteria: WHEN data is cached THEN the dashboard initial render SHALL complete under 2s on broadband with typical (<5k activities) datasets.

R30. Accessibility
> User Story: As a user, I want an accessible UI so that I can use the app with assistive tech.
> Acceptance Criteria: WHEN using the UI THEN charts and controls SHALL be keyboard navigable, color‑contrast compliant, and include tooltips/legends.

R31. Observability
> User Story: As an operator, I want basic logging and error tracking so that I can troubleshoot.
> Acceptance Criteria: WHEN requests/processes occur THEN the system SHALL log key events and capture errors with minimal PII.

## 8. UI & Endpoints

R32. Required Pages
> User Story: As a user, I want clear navigation so that I can connect and view my data.
> Acceptance Criteria: WHEN using the app THEN I SHALL see: Index/Login with “Connect with Strava”, Dashboard with listed widgets, Date controls, Unit toggle, and Sign‑out; and an Error page.

R33. Backend API (if applicable)
> User Story: As a developer, I want a summarized endpoint so that the UI loads efficiently.
> Acceptance Criteria: WHEN the UI requests data THEN the system SHALL provide a summary endpoint (or per‑widget endpoints) with granularity parameters as defined.

R34. ISO Week Rule Documentation
> User Story: As a user, I want consistent weekly aggregation so that comparisons are valid.
> Acceptance Criteria: WHEN showing weekly trends THEN the system SHALL use a documented rule (e.g., ISO weeks Mon–Sun) consistently across widgets and tooltips.

## 9. Configuration & Deployment

R35. Environment Configuration
> User Story: As an operator, I want environment‑specific settings so that deployments are reliable.
> Acceptance Criteria: WHEN running in dev/staging/prod THEN the system SHALL read environment variables for client IDs/secrets, redirect URLs, base URL, session secret, log level, cache TTLs, and rate‑limit policies.

R36. Data Deletion/Retention
> User Story: As a privacy‑minded user, I want control over cached data so that my data isn’t stored unnecessarily.
> Acceptance Criteria: WHEN I request cache deletion THEN the system SHALL purge stored activity caches for my account and cease retention beyond configured TTLs.
