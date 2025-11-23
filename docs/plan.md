 # Development Plan — Strava Activity Analyzer (PHP)
 
 Generated from docs/requirements.md on 2025-11-22.
 
 ## 1) Overview
 The goal is to build a read‑only web application that authenticates with a user’s Strava account (OAuth2) and presents interactive analytics of their activities: overview distributions, heatmaps, trends, and running‑specific stats. The app must keep tokens secure, respect Strava API limits, avoid mutating user data, and compute metrics dynamically within user‑selected date ranges and unit preferences.
 
 This plan translates the requirements (sections 1–12) into deliverable phases with concrete tasks, dependencies, and risks.
 
 ## 2) Scope assumptions and tech stack (adjustable)
 - Language/runtime: PHP 8.2+ with Composer
 - Web framework: Slim or Laravel (choose one; plan below uses neutral terms like “router”, “middleware”)  
   • If Slim: `slim/slim`, `slim/psr7`, DI via `php-di/php-di`  
   • If Laravel: built‑in routing, middleware, config, views
 - HTTP and OAuth2: `league/oauth2-client` (or direct Strava OAuth flow) + PSR‑18 HTTP client (`guzzlehttp/guzzle`)
 - Charts: Chart.js (simple), or D3 (advanced). Chart.js recommended for speed.
 - Views: Blade/Twig or simple PHP templates; Vite/ESBuild for bundling assets if needed.
 - Session store: PHP session (file/redis). Prefer Redis for scalability.
 - Caching: In‑memory (per request), session cache, and optional Redis cache of fetched pages from Strava within TTL to reduce API calls.
 - Timezones: `ext-intl` and `nesbot/carbon` for date handling.
 - Deployment: Dockerfile + container‑friendly PHP‑FPM with Nginx, or a managed PHP host. Env via `.env` (never commit secrets).
 
 ## 3) Architecture at a glance
 - Client (browser): SPA‑like navigation per tab, charts rendered via Chart.js. URL carries date filter and unit prefs for persistence.
 - Server (PHP):
   - Routes: `/` (dashboard), `/auth/strava` (init), `/auth/callback` (OAuth callback), `/signout`, `/api/*` for data endpoints (JSON for charts), health `/healthz`.
   - Middleware: session, auth‑guard (ensures tokens exist), CSRF for form posts (not much needed here), error handler.
   - Services: `OAuthService`, `StravaClient`, `ActivityService` (fetch + normalize + cache), `AggregationService` (metrics, gaps, PRs), `UnitsService` (imperial/metric), `TrendsService`, `HeatmapService`.
   - Storage: tokens server‑side (session/DB/Redis). No long‑term user data persistence by default; optional cached pages from Strava with short TTL for performance.
 
 ## 4) Data flow
 1) User clicks “Connect with Strava” → redirect to Strava OAuth (scopes: `read`, `activity:read_all`).  
 2) Callback exchanges code for access + refresh tokens; tokens stored server‑side.  
 3) User lands on dashboard; client requests `/api/summary`, `/api/distributions`, etc., passing date range and unit prefs.  
 4) Server uses tokens to call Strava with pagination, rate‑limit awareness, normalizes timezones, computes aggregations, returns JSON.  
 5) Front‑end renders charts and heatmaps; URL reflects filters and prefs.
 
 ## 5) Phased implementation plan
 
 ### Phase 0 — Project setup & foundations
 - Initialize Composer, framework skeleton, PSR‑4 autoloading.
 - Configure env: `STRAVA_CLIENT_ID`, `STRAVA_CLIENT_SECRET`, `STRAVA_REDIRECT_URI`, `APP_URL`, `SESSION_SECRET`, `CACHE_URL`.
 - Add HTTP client, OAuth2, date libs, Chart.js, build tooling (optional Vite).  
 - Base routing, error handler, logging.  
 - Health endpoint `/healthz`.
 
 Deliverables: running stub app with home page and health check.
 
 ### Phase 1 — Authentication & Security (Reqs 1–2)
 - Route GET `/auth/strava` → build auth URL with scopes `read,activity:read_all`; state + PKCE optional; redirect.
 - Route GET `/auth/callback` → exchange code for access/refresh tokens; store server‑side; redirect to dashboard.
 - Token refresh: middleware/service to auto‑refresh on 401/expiry.  
 - `Sign Out`: POST/GET `/signout` → clear server session; invalidate token storage.
 - Never expose tokens to the browser; only server holds them.
 
 Deliverables: full OAuth loop, session persistence, sign‑out.
 
 ### Phase 2 — Data acquisition & normalization (Req 3)
 - `StravaClient`: endpoints for activities list with pagination; honor rate limits (read headers; exponential backoff and retry windows).  
 - Fetch only necessary fields to reduce payload.  
 - Normalize timestamps to user’s local timezone (detect from Strava profile or allow UI selection; default browser tz).
 - Cache pages within a short TTL (e.g., 2–5 minutes) to minimize repeated API calls when switching tabs.
 
 Deliverables: service returning a normalized in‑memory representation of activities for a requested date window.
 
 ### Phase 3 — Domain modeling & aggregation services
 - Define `Activity` model (id, type, start_at_local, moving_time, distance, pace, elevation_gain, etc.).
 - Aggregations:  
   • Counts and time per sport type (Overview, Time Distribution).  
   • Running stats: totals, >10K count, avg pace, PRs (fastest mile, fastest 10K, longest run, most elevation gain).  
   • Gaps & streaks: compute workout days, missed days, current streak, days since last, longest gap, total gap days; enumerate gap intervals.  
   • Trends: daily/weekly/monthly aggregation, moving average smoothing.  
 - Unit conversion utilities (imperial/metric) and pace handling (speed ↔ pace).
 
 Deliverables: pure‑PHP services with unit tests for calculation logic.
 
 ### Phase 4 — Dashboard shell & navigation (Req 4)
 - Header with “Strava Stats” brand → navigates to dashboard home `/`.
 - Summary strip above tabs: Date Range (start–end), Total Activities, Total Moving Time.  
 - Tabs: Overview, Duration, Heatmap, Trends, Running Stats (exact order).
 
 Deliverables: server-rendered shell w/ client JS for tab content.
 
 ### Phase 5 — API endpoints for widgets
 - `/api/summary?from=...&to=...&units=...`
 - `/api/distributions?from=...&to=...&units=...` (counts/time by type)
 - `/api/heatmap?from=...&to=...&mode=all|running&units=...` (calendar grid + legend buckets)
 - `/api/trends?from=...&to=...&mode=all|running&grain=day|week|month&units=...`
 - `/api/running-stats?from=...&to=...&units=...`
 - `/api/gaps?from=...&to=...&units=...` (for “Show Gap Details”)
 
 Deliverables: documented JSON contracts consumed by the front‑end.
 
 ### Phase 6 — Overview & Duration widgets (Req 5)
 - Overview tab: Pie/Donut of activity counts by type, with data labels for segments >5%.
 - Duration tab: Chart of total moving time aggregated by type; labels for segments >5%.
 
 Deliverables: responsive charts using Chart.js; unit tests for server JSON shape.
 
 ### Phase 7 — Heatmap (Req 6)
 - Toggle between All Activities (color by total time/day) and Running Only (color by distance/day).  
 - Horizontal calendar layout (days as rows, weeks as columns); responsive width.  
 - Legend: All Activities → No Activity, <1h, 1–2h, 2h+; Running Only → distance buckets (define thresholds, e.g., 0, <3mi, 3–6mi, 6mi+ or km equivalents).  
 - Stats: workout days, missed days, current streak, days since last, longest gap, total gap days.  
 - “Show Gap Details” → list intervals with start, end, duration.
 
 Deliverables: heatmap UI + JSON API powering it.
 
 ### Phase 8 — Running Statistics (Req 7)
 - Order: summary first, then distance distribution histogram (1‑mile or 1‑km bins).  
 - Summary: total runs, count >10K, total distance, average pace.  
 - PRs: fastest mile, fastest 10K, longest run, most elevation gain. Handle missing data gracefully.
 
 Deliverables: server computation + charts and histogram.
 
 ### Phase 9 — Trends (Req 8)
 - Toggle All vs Running Only.  
 - Line charts of distance and pace over time aggregated by day/week/month.  
 - Apply smoothing (moving average configurable window, default 7 days for daily series).  
 - Correctly handle pace as time/distance (invert for averaging, avoid naive mean).
 
 Deliverables: trend time series, smoothing, UI toggle.
 
 ### Phase 10 — User controls & persistence (Reqs 9–10)
 - Date presets: 7 days (default), 30 days, YTD, All Time; custom picker for start/end.  
 - Units toggle: default imperial (miles, min/mile). Convert distances and paces immediately on toggle.  
 - Persist filters and units in URL (query params) and session for refresh survival.
 
 Deliverables: control bar, URL/state sync, conversions.
 
 ### Phase 11 — Error handling, empty states, UX polish (Reqs 11–12)
 - Empty range → friendly “No activities found.”  
 - API/network errors → descriptive message + retry.  
 - Performance targets:  
   • Initial render < 2s for typical dataset (<5,000 activities).  
   • Filtered chart updates < 300ms if data cached.  
 - Loading spinners/skeletons; lazy load tab data.
 
 Deliverables: resilient UX meeting performance objectives.
 
 ### Phase 12 — Observability, security hardening, and compliance
 - Logging with correlation IDs per request; log Strava rate‑limit headers and retries.  
 - Metrics: request counts, latency, cache hit rate.  
 - Security: HTTPS, secure cookies, SameSite, CSRF (where relevant), input validation for query params, output escaping in views.  
 - Privacy: no client‑side tokens, minimal retention; document data handling in README.
 
 Deliverables: logs + basic metrics, security checklist.
 
 ### Phase 13 — Testing & QA
 - Unit tests: aggregation math (PRs, gaps, trends, unit conversions).  
 - Integration tests: OAuth callback handling (mock Strava), pagination, rate‑limit backoff.  
 - Contract tests: API JSON schemas.  
 - UI tests (optional): Cypress/Playwright for critical flows.
 
 Deliverables: passing test suite on CI.
 
 ### Phase 14 — Packaging & Deployment
 - Dockerfile (PHP‑FPM + Nginx), production config, env injection.  
 - CI: build, lint, run tests; deploy on tag.  
 - Target hosting: any PHP‑capable container platform; configure secrets.
 
 Deliverables: deployed staging environment with OAuth callback URL registered in Strava app settings.
 
 ## 6) Mapping to requirements
 - 1–2 Authentication & Security → Phases 1, 12
 - 3 Data Acquisition → Phases 2, 3
 - 4 Dashboard Navigation → Phases 4, 5
 - 5 Activity Distribution → Phases 6, 5
 - 6 Heatmap → Phases 7, 5
 - 7 Running Stats → Phases 8, 5
 - 8 Trends → Phases 9, 5
 - 9 Date Filtering → Phases 10, 5
 - 10 Unit Preferences → Phases 10, 3
 - 11 Error Handling → Phase 11
 - 12 Performance → Phase 11 (and 2, 5 for caching)
 
 ## 7) Dependencies
 - Strava developer app with client id/secret and redirect URI.  
 - Composer packages: `guzzlehttp/guzzle`, `league/oauth2-client`, `nesbot/carbon`, framework libs.  
 - Chart.js via npm or CDN.  
 - Optional: Redis for session/cache; a simple DB (SQLite/MySQL) only if persisting anything beyond tokens/sessions.
 
 ## 8) Risks and mitigations
 - Strava API rate limits: implement header‑aware backoff; cache pages; debounce UI requests.  
 - Timezone correctness: normalize to user local; test around DST transitions.  
 - Pace calculations: use speed ↔ pace conversions; avoid averaging paces directly.  
 - Large activity histories: paginate efficiently; pre‑aggregate in memory per request window; stream results to client.  
 - OAuth edge cases: expired codes, revoked tokens → clear session, prompt reconnect.  
 - Unit toggling drift: ensure exact conversions; centralize in `UnitsService`.  
 - Heatmap performance: precompute bins; draw via canvas not DOM for speed.  
 - Privacy/security: never leak tokens; secure cookies; enforce HTTPS; threat‑model callback endpoint.
 
 ## 9) Acceptance and verification
 - Each requirement’s acceptance criteria are validated against API responses and UI behavior in staging.  
 - Performance budgets profiled with synthetic datasets up to 5,000 activities.  
 - Test evidence recorded in CI artifacts.
 
 ## 10) Out of scope (initial release)
 - Social features (following, kudos, comments).  
 - Writing/mutating data on Strava.  
 - Long‑term data warehousing.
 
 ## 11) Milestones (indicative)
 - M1: Phases 0–1 (OAuth loop) — 1–2 days
 - M2: Phases 2–3 (data + aggregations) — 2–4 days
 - M3: Phases 4–6 (dashboard + distributions) — 2–3 days
 - M4: Phases 7–9 (heatmap, running stats, trends) — 3–5 days
 - M5: Phases 10–12 (controls, UX, security/obs) — 2–3 days
 - M6: Phases 13–14 (tests, deploy) — 2–3 days
 
 ---
 This plan intentionally avoids writing application code and focuses on the sequence of deliverables to meet the documented requirements.