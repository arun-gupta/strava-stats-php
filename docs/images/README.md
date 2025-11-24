# Documentation Images

This directory contains screenshots and images for the project documentation.

## Recommended Images

### Application Screenshots

1. **home-page.png** - Landing page with "Connect with Strava" button
2. **oauth-authorize.png** - Strava OAuth authorization screen
3. **dashboard-overview.png** - Overview tab with activity pie chart
4. **dashboard-duration.png** - Duration tab with time breakdown
5. **dashboard-heatmap.png** - Heatmap tab with calendar view
6. **dashboard-running.png** - Running stats tab with PRs and histogram
7. **dashboard-trends.png** - Trends tab with distance and pace graphs
8. **dashboard-filters.png** - Date range filters (7/30/90 days, YTD, custom)

### Feature Screenshots

9. **mobile-view.png** - Responsive mobile layout
10. **error-handling.png** - Error page with recovery options
11. **loading-state.png** - Loading spinner/skeleton
12. **empty-state.png** - Dashboard with no activities

### Developer Documentation

13. **architecture-diagram.png** - System architecture overview
14. **oauth-flow.png** - OAuth flow diagram
15. **data-flow.png** - Data flow from API to dashboard

## Image Guidelines

- **Format:** PNG for screenshots, SVG for diagrams
- **Size:** Max width 1200px for screenshots
- **Quality:** Use high-quality captures (Retina/2x recommended)
- **Privacy:** Redact any personal information before committing
- **Naming:** Use lowercase with hyphens (kebab-case)

## How to Add Images

1. Take screenshot or create diagram
2. Save with descriptive name in this directory
3. Reference in markdown files using relative path:
   ```markdown
   ![Dashboard Overview](images/dashboard-overview.png)
   ```

## Current Images

- **overview.png** (230KB) - Dashboard Overview tab with summary cards and activity distribution pie chart
- **duration.png** (122KB) - Duration tab showing time spent by activity type with insights
- **heatmap-activity.png** (97KB) - Heatmap tab with activity calendar and workout statistics (All Activities mode)
- **heatmap-running.png** (98KB) - Heatmap tab showing running-only activity calendar
- **running-stats.png** (201KB) - Running Stats tab with pace metrics, PRs, and distance distribution
- **trends.png** (207KB) - Trends tab with distance and pace trend charts over time

## Tools for Screenshots

- **macOS:** Cmd+Shift+4 (select area), Cmd+Shift+3 (full screen)
- **Chrome DevTools:** Cmd+Shift+P → "Capture screenshot"
- **Responsive Testing:** Use Chrome DevTools device toolbar

## Tools for Diagrams

- [Excalidraw](https://excalidraw.com/) - Simple hand-drawn diagrams
- [Draw.io](https://draw.io/) - Professional diagrams
- [Mermaid](https://mermaid.js.org/) - Text-based diagrams (can be embedded in markdown)

---

Last Updated: 2025-11-23
