# Technical Task Checklist

Last updated: 2025-11-17

Each task links to Plan item(s) P# (see docs/plan.md) and Requirement(s) R# (see docs/requirements.md). Mark completed tasks with [x]. Keep links intact.

## Phase 1 — Setup & Foundations
1. [ ] Initialize repository config: env loading, secrets management scaffold (P1; R35, R5)
2. [ ] Document required env vars and sample .env.* files (P1; R35)
3. [ ] Set up logging utility with minimal PII and levels (P3, P14, P15; R31)
4. [ ] Establish time/locale library and ISO week rule doc (P4; R10, R24, R34)
5. [ ] Implement unit conversion helpers and tests (P5; R23, R16, R17, R19, R20)

## Phase 2 — Authentication
6. [ ] Implement OAuth2 Authorization Code flow with PKCE or server secret (P2; R1, R4, R6)
7. [ ] Callback handler: code exchange, token storage (server‑side), state validation (P2; R1, R2, R6)
8. [ ] Token refresh logic (background and on 401) (P2; R2, R31)
9. [ ] Scope disclosure UI on connect screen (P2; R3)
10. [ ] Sign‑out route: clear session/tokens (P2; R32)

## Phase 3 — Strava Client & Data Layer
11. [ ] Create typed Strava API client with pagination (P3; R7)
12. [ ] Add retry/backoff with rate‑limit awareness (P3; R8, R27)
13. [ ] Implement caching layer with configurable TTLs (P3; R9, R29)
14. [ ] Respect privacy scopes: include/exclude private activities (P3; R11)
15. [ ] Error surfaces for API/network failures (P3, P13; R26)

## Phase 4 — Analytics Engine
16. [ ] Activity count by type aggregator (P6; R12)
17. [ ] Time by type aggregator with HH:MM formatting (P6; R13)
18. [ ] Running totals (total runs, ≥10K, total distance, avg pace) (P6; R16)
19. [ ] Distance histogram with configurable bins/range (P6; R17)
20. [ ] Mileage trends: daily/weekly/monthly + smoothing (P6; R19, R34)
21. [ ] Pace trends with speed→pace inversion (P6; R20)
22. [ ] Heatmap computations for workouts + streaks (P7; R14)
23. [ ] Heatmap computations for runs + streaks (P7; R15)
24. [ ] Personal records computation with splits/approx fallback (P8; R18)

## Phase 5 — API (if SPA+API architecture)
25. [ ] Implement GET /api/me/summary (from, to, units) (P10; R33)
26. [ ] Optional: per‑widget endpoints (P10; R33)

## Phase 6 — UI & UX
27. [ ] Build Index/Login page with “Connect with Strava” and scope disclosure (P11, P2; R32, R3)
28. [ ] Build Dashboard layout with responsive grid and accessible colors (P11; R30, R32)
29. [ ] Date filter controls: presets and custom pickers (P9; R21, R22)
30. [ ] Unit toggle control wired to conversion service (P5, P12; R23)
31. [ ] Widget: Activity Count Distribution (P12; R12)
32. [ ] Widget: Time Distribution (P12; R13)
33. [ ] Widget: Workout Heatmap with streaks (P12; R14)
34. [ ] Widget: Running Heatmap with streaks (P12; R15)
35. [ ] Widget: Running Stats (totals, histogram, PRs) (P12; R16, R17, R18)
36. [ ] Widget: Mileage Trend (P12; R19)
37. [ ] Widget: Pace Trend (P12; R20)
38. [ ] Error page and standardized error/empty components (P13; R25, R26, R27, R28)

## Phase 7 — Performance & Testing
39. [ ] Cache‑first loading path; verify <2s initial render on typical dataset (P14; R29)
40. [ ] Integration test: OAuth callback with mocked Strava (P14; R1, R2)
41. [ ] Unit tests: all aggregators and utilities (P14; R12–R21)
42. [ ] Tests: rate‑limit/retry logic (P14; R8)
43. [ ] UI e2e: date presets, custom range persistence, unit toggle, widgets render (P14; R21–R23, R32)

## Phase 8 — Deployment & Ops
44. [ ] Environment configs for dev/staging/prod and HTTPS guidance (P15; R35)
45. [ ] Cache purge endpoint/mechanism per user (P15; R36)
46. [ ] Error tracking wiring and operational dashboards/logs (P15; R31)

## Phase 9 — Documentation
47. [ ] Update README with setup, run, and env configuration (P1, P15; R35)
48. [ ] Document ISO week rule, timezone normalization, and data retention policy (P4, P15; R10, R34, R36)
