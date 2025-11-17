# Implementation Plan

Last updated: 2025-11-17

This plan operationalizes the Requirements (docs/requirements.md). Each plan item P# links to specific requirement IDs (R#) and carries a priority.

Legend: Priority = High (H), Medium (M), Low (L)

## P1. Project Setup & Configuration (H)
- Link: R35, R5
- Actions:
  - Initialize environment config (client IDs/secrets, redirect URIs, base URL, session secret, log level, cache TTLs, rate limit policy).
  - Establish secure secret handling; ensure no secrets are bundled into client assets.

## P2. OAuth2 Authentication Module (H)
- Link: R1, R2, R3, R4, R6, R28, R32
- Actions:
  - Authorization Code flow with PKCE or server‑side secret: redirect to Strava, handle callback, exchange code, store tokens.
  - Token refresh flow with automatic refresh upon expiry/401.
  - Display requested scopes clearly on the consent initiation page.
  - Implement state parameter and CSRF protections; secure cookies.
  - Re‑auth flow on revoked/invalid tokens preserving intended route.

## P3. Strava API Client & Rate Limit Handling (H)
- Link: R7, R8, R9, R11, R26, R27, R31
- Actions:
  - Typed client for Strava endpoints (auth, activities list, splits/laps if used).
  - Centralized retry/backoff with rate‑limit awareness; bubble friendly messages.
  - Caching layer (in‑memory or persistent) with configurable TTLs; cache keys include user, range, and widget.
  - Respect privacy scopes (include/exclude private activities accordingly).
  - Logging of key events; minimal PII.

## P4. Time & Locale Services (H)
- Link: R10, R24, R34
- Actions:
  - Timezone normalization utilities for daily/weekly/monthly aggregations.
  - ISO week rule (Mon–Sun) documented and applied consistently.
  - Locale‑aware formatting for dates/numbers.

## P5. Units & Conversion Utilities (H)
- Link: R23, R16, R17, R19, R20
- Actions:
  - Bi‑directional conversions: meters↔km↔miles; seconds↔HH:MM:SS; m/s↔pace.
  - Single source of truth service for unit preference with toggle integration.

## P6. Analytics Engine: Core Aggregations (H)
- Link: R12, R13, R16, R17, R19, R20, R34
- Actions:
  - Implement reducers for: activity count by type, time by type, running totals, distance histogram, mileage and pace trends with smoothing, weekly/monthly grouping.
  - Handle pace inversion robustly.

## P7. Analytics Engine: Heatmaps & Streaks (M)
- Link: R14, R15
- Actions:
  - Calendar‑grid computation for workouts and runs with intensity levels.
  - Current and longest streak calculations for any‑activity and runs.

## P8. Analytics Engine: Personal Records (M)
- Link: R18
- Actions:
  - Compute fastest mile, fastest 10K, longest run, max elevation in a run using split/segment data when available; otherwise approximate.

## P9. Date Filter Controls & State Persistence (H)
- Link: R21, R22
- Actions:
  - Preset ranges (7d, 30d, 90d, 6m, 1y, YTD, All Time).
  - Custom date pickers; persist in URL query or server session; restore on load.

## P10. API Design (if using SPA+API) (M)
- Link: R33
- Actions:
  - Provide GET /api/me/summary with from/to/units parameters returning all widget data; optionally per‑widget endpoints.

## P11. UI: Pages & Layout (H)
- Link: R32, R30
- Actions:
  - Pages: Index/Login, Dashboard, Error.
  - Responsive layout; chart containers; accessible color palette and keyboard navigation.

## P12. UI: Dashboard Widgets (H)
- Link: R12, R13, R14, R15, R16, R17, R18, R19, R20, R23
- Actions:
  - Implement seven widgets; connect to summary data; include unit toggle control and tooltips/legends.

## P13. Error & Empty State UX (H)
- Link: R25, R26, R27, R28
- Actions:
  - Standardized error surfaces and empty components; retry affordances; re‑auth prompts.

## P14. Performance, Testing, and Quality (H)
- Link: R29, R31, R26, R27, R33
- Actions:
  - Cache‑first loading path for sub‑2s render on typical datasets.
  - Tests: OAuth callback integration (mock Strava), aggregation unit tests, rate‑limit/retry tests, UI e2e for filters/widgets.

## P15. Deployment & Operations (M)
- Link: R35, R36, R31
- Actions:
  - Environment‑specific config, secrets management, HTTPS in prod.
  - Cache purge endpoint/mechanism per user; basic request logging and error tracking.

## Coverage Matrix (Summary)
- Authentication & Security: P1, P2
- Data Acquisition & Reliability: P3, P4
- Analytics & Widgets: P5, P6, P7, P8, P12
- Filters & i18n: P4, P5, P9
- UI & API: P10, P11, P12, P13
- Quality & Ops: P14, P15
