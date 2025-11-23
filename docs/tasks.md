# Tasks — Strava Activity Analyzer (PHP)

Generated from docs/plan.md on 2025-11-23. Do not implement tasks here; this file is a checklist only.

1. Phase 0 — Project setup & foundations
   - [ ] Decide framework (Slim or Laravel) and record the decision and rationale in README.
   - [ ] Initialize Composer project and configure PSR-4 autoloading for the app namespace.
   - [ ] Create base project structure (src/, public/, config/, routes/, views/ or framework defaults).
   - [ ] Add Composer dependencies: guzzlehttp/guzzle, league/oauth2-client, nesbot/carbon, and chosen framework packages.
   - [ ] Set up front-end dependency for Chart.js (via npm/CDN) and, if chosen, bundler (Vite/ESBuild) with basic config.
   - [ ] Create .env and .env.example with STRAVA_CLIENT_ID, STRAVA_CLIENT_SECRET, STRAVA_REDIRECT_URI, APP_URL, SESSION_SECRET, CACHE_URL.
   - [ ] Implement base routing and a centralized error handler aligned with framework conventions.
   - [ ] Configure application logging (format, level, destination).
   - [ ] Implement /healthz endpoint returning simple JSON status.
   - [ ] Add minimal home page placeholder (dashboard shell scaffold).

2. Phase 1 — Authentication & Security (Reqs 1–2)
   - [ ] Implement GET /auth/strava to generate OAuth URL with scopes read,activity:read_all (include state and PKCE if used).
   - [ ] Implement GET /auth/callback to exchange code for access and refresh tokens with Strava.
   - [ ] Store tokens server-side (session/Redis) and never expose tokens to the browser.
   - [ ] Add token refresh middleware/service that auto-refreshes on expiry/401 and retries the original request.
   - [ ] Implement /signout to clear server session and discard token storage.
   - [ ] Verify OAuth loop end-to-end manually and document setup steps (Strava app settings, redirect URI) in README.

3. Phase 2 — Data acquisition & normalization (Req 3)
   - [ ] Implement StravaClient for activities list with pagination support.
   - [ ] Read and honor Strava rate-limit headers; add exponential backoff/retry with jitter.
   - [ ] Fetch only required fields to minimize payload.
   - [ ] Normalize activity timestamps to user local timezone (detect from profile or accept UI/browser tz).
   - [ ] Add short‑TTL caching (e.g., 2–5 minutes) for fetched pages to reduce repeat calls.
   - [ ] Add configuration for pagination size, backoff parameters, and cache TTL.

4. Phase 3 — Domain modeling & aggregation services
   - [ ] Define Activity model (id, type, start_at_local, moving_time, distance, pace, elevation_gain, etc.).
   - [ ] Implement AggregationService: counts and time per sport type (Overview, Duration).
   - [ ] Implement running summary stats: total runs, count of runs >10K, total distance, average pace.
   - [ ] Implement PR calculations: fastest mile, fastest 10K, longest run, most elevation gain (handle missing data).
   - [ ] Implement gaps & streaks: workout days, missed days, current streak, days since last, longest gap, total gap days; compute gap intervals.
   - [ ] Implement TrendsService: daily/weekly/monthly aggregation and moving-average smoothing.
   - [ ] Implement UnitsService: metric/imperial conversions and robust pace (speed ↔ pace) handling.
   - [ ] Create unit tests for all aggregation and conversion logic.

5. Phase 4 — Dashboard shell & navigation (Req 4)
   - [ ] Build header with “Strava Stats” brand linking to dashboard home (/).
   - [ ] Implement summary strip: Date Range (start–end), Total Activities, Total Moving Time.
   - [ ] Create tabbed navigation in this exact order: Overview, Duration, Heatmap, Trends, Running Stats.
   - [ ] Implement server-rendered shell and client JS bootstrapping for tab content loading.

6. Phase 5 — API endpoints for widgets
   - [ ] Implement /api/summary?from=...&to=...&units=....
   - [ ] Implement /api/distributions?from=...&to=...&units=... (counts/time by type).
   - [ ] Implement /api/heatmap?from=...&to=...&mode=all|running&units=... (calendar grid + legend buckets).
   - [ ] Implement /api/trends?from=...&to=...&mode=all|running&grain=day|week|month&units=....
   - [ ] Implement /api/running-stats?from=...&to=...&units=....
   - [ ] Implement /api/gaps?from=...&to=...&units=... (for “Show Gap Details”).
   - [ ] Document JSON response contracts (schemas) for all endpoints and add to repo (e.g., OpenAPI/JSON Schema).

7. Phase 6 — Overview & Duration widgets (Req 5)
   - [ ] Implement Overview pie/donut chart of activity counts by type in UI.
   - [ ] Add data labels for segments >5% of total on Overview chart.
   - [ ] Implement Duration chart of total moving time aggregated by type.
   - [ ] Add data labels for segments >5% of total on Duration chart.
   - [ ] Ensure API responses match expected shapes; add unit tests for server JSON shape.

8. Phase 7 — Heatmap (Req 6)
   - [ ] Implement mode toggle: All Activities vs Running Only.
   - [ ] Render horizontal calendar layout (days as rows, weeks as columns) responsive to screen width.
   - [ ] Implement color scale/legend: All Activities → No Activity, <1h, 1–2h, 2h+.
   - [ ] Implement color scale/legend: Running Only → distance buckets (e.g., 0, <3mi, 3–6mi, 6mi+ with km equivalents).
   - [ ] Display workout stats: workout days, missed days, current streak, days since last, longest gap, total gap days.
   - [ ] Implement “Show Gap Details” panel listing gap intervals (start date, end date, duration).
   - [ ] Optimize rendering (prefer canvas) and precompute bins for performance.

9. Phase 8 — Running Statistics (Req 7)
   - [ ] Render running summary first: total runs, count >10K, total distance, average pace.
   - [ ] Render distance distribution histogram with 1‑mile (or 1‑km) bins.
   - [ ] Display PRs: fastest mile, fastest 10K, longest run, most elevation gain.
   - [ ] Handle missing or partial data gracefully in UI and computations.

10. Phase 9 — Trends (Req 8)
   - [ ] Add toggle for All Activities vs Running Only.
   - [ ] Render line charts of distance and pace aggregated by day/week/month.
   - [ ] Apply moving-average smoothing (default 7 days for daily series; make window configurable).
   - [ ] Ensure pace is handled correctly (invert for averaging; avoid naive mean).

11. Phase 10 — User controls & persistence (Reqs 9–10)
   - [ ] Implement date presets: 7 days (default), 30 days, YTD, All Time.
   - [ ] Implement custom date range picker for start and end dates.
   - [ ] Implement units toggle, defaulting to imperial (miles, min/mile) on first load.
   - [ ] Convert all distance and pace metrics immediately on unit toggle.
   - [ ] Persist date filters and unit preferences in URL query params and session so refresh preserves state.

12. Phase 11 — Error handling, empty states, UX polish (Reqs 11–12)
   - [ ] Show friendly “No activities found” message when a date range has no activities.
   - [ ] Display descriptive API/network error messages with a retry action.
   - [ ] Add loading spinners/skeletons and lazy-load tab data.
   - [ ] Meet performance targets: initial render < 2s for <5,000 activities; cached filter updates < 300ms.
   - [ ] Profile and address bottlenecks (server and client) to meet budgets.

13. Phase 12 — Observability, security hardening, and compliance
   - [ ] Add correlation IDs per request and log Strava rate-limit headers and retries.
   - [ ] Expose basic metrics (request counts, latency, cache hit rate).
   - [ ] Enforce HTTPS, secure cookies, SameSite, and CSRF where applicable.
   - [ ] Validate/clean query params and escape outputs in views.
   - [ ] Document privacy posture in README (no client-side tokens; minimal retention).

14. Phase 13 — Testing & QA
   - [ ] Add unit tests for aggregation math (PRs, gaps, trends, conversions).
   - [ ] Add integration tests for OAuth callback handling (mock Strava), pagination, and rate-limit backoff.
   - [ ] Add contract tests validating API JSON schemas.
   - [ ] (Optional) Add UI tests (Cypress/Playwright) for critical flows.
   - [ ] Configure test data fixtures including synthetic datasets up to 5,000 activities.

15. Phase 14 — Packaging & Deployment
   - [ ] Create Dockerfile (PHP-FPM + Nginx) and production configuration.
   - [ ] Set up CI pipeline to build, lint, run tests, and produce artifacts.
   - [ ] Configure deployment on tag to target PHP-capable container platform.
   - [ ] Register OAuth redirect URL in Strava developer app settings (staging/prod).
   - [ ] Deploy staging environment and perform smoke tests of OAuth and API endpoints.

16. Acceptance & verification (cross-cutting)
   - [ ] Map each requirement’s acceptance criteria to test cases and verify in staging.
   - [ ] Capture performance profiles meeting stated budgets and store results as CI artifacts.
   - [ ] Update README with setup, environment variables, and operational notes.
