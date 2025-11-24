# Performance Testing Scripts

This directory contains scripts for profiling and load testing the Strava Activity Analyzer.

## Available Scripts

### 1. Dashboard Performance Profiler

**File:** `performance_profile.php`
**Wrapper:** `profile_dashboard.sh`

Comprehensive profiling tool that measures execution time and memory usage for all dashboard operations.

#### Usage:

```bash
# Method 1: Using the shell script (recommended)
./scripts/profile_dashboard.sh <access_token>

# Method 2: Direct PHP execution
php scripts/performance_profile.php <access_token>

# Method 3: Using .env configuration
echo 'STRAVA_TEST_TOKEN=your_token_here' >> .env
./scripts/profile_dashboard.sh
```

#### How to get your access token:

1. Log in to the application at http://localhost:8000
2. Open browser developer tools (F12)
3. Go to **Application** > **Storage** > **Session Storage**
4. Look for `access_token` key and copy its value

#### What it measures:

- ✓ Strava API call times (athlete profile, activities)
- ✓ Data parsing and processing
- ✓ Date range filtering
- ✓ Activity statistics calculation
- ✓ Heatmap generation
- ✓ Running-specific calculations
- ✓ Trend analysis
- ✓ Memory usage per operation
- ✓ Session storage size

#### Output:

```
╔════════════════════════════════════════════════════════════════════════════╗
║                     DASHBOARD PERFORMANCE PROFILER                         ║
╚════════════════════════════════════════════════════════════════════════════╝

EXECUTION TIMES:
--------------------------------------------------------------------------------
Operation                                   Time (ms)       Memory (KB)
--------------------------------------------------------------------------------
Strava API: Fetch athlete profile              245.32           128.50
Strava API: Fetch activities (page 1)          412.18           256.75
Parse activities into models                     8.45            64.25
Filter activities by date range                  2.31            12.00
Calculate activity counts by type                1.22             4.50
...
--------------------------------------------------------------------------------
TOTAL TIME                                    1,234.56

PERFORMANCE TARGETS:
--------------------------------------------------------------------------------
Dashboard Load Time: 1234.56 ms (Target: < 2000 ms) ✓ PASS
================================================================================
```

---

### 2. Filter Performance Measurement

**File:** `measure_filter_performance.php`

Tests the performance of date range filtering operations with cached data to ensure they meet the < 300ms target.

#### Usage:

```bash
php scripts/measure_filter_performance.php <access_token>
```

#### What it measures:

- ✓ 7 days filter performance
- ✓ 30 days filter performance
- ✓ 90 days filter performance
- ✓ 6 months filter performance
- ✓ Year-to-date filter performance
- ✓ Custom date range performance

#### Output:

```
╔════════════════════════════════════════════════════════════════════════════╗
║                   FILTER PERFORMANCE MEASUREMENT                           ║
╚════════════════════════════════════════════════════════════════════════════╝

Running filter performance tests with 147 cached activities...

  7 days:   45.23 ms ✓ PASS
  30 days:  78.91 ms ✓ PASS
  90 days:  125.67 ms ✓ PASS
  6 months: 198.34 ms ✓ PASS
  YTD:      156.78 ms ✓ PASS
  Custom:   89.45 ms ✓ PASS

--------------------------------------------------------------------------------
Average:  115.73 ms
Min:      45.23 ms
Max:      198.34 ms
--------------------------------------------------------------------------------

PERFORMANCE TARGET: < 300ms
Pass Rate: 100% (6/6 tests)
Status: ✓ ALL TESTS PASSED
```

---

### 3. Load Testing

**File:** `load_test.sh`

Apache Bench-based load testing script for testing dashboard performance under various load conditions.

#### Prerequisites:

```bash
# Install Apache Bench
# macOS:
brew install httpd

# Ubuntu/Debian:
sudo apt-get install apache2-utils

# CentOS/RHEL:
sudo yum install httpd-tools
```

#### Usage:

```bash
# Test public endpoints only
./scripts/load_test.sh

# Test with specific host
./scripts/load_test.sh --host http://localhost:8000

# Test authenticated endpoints (requires session ID)
./scripts/load_test.sh --session <PHPSESSID>

# Full test with custom host and session
./scripts/load_test.sh --host http://localhost:8000 --session <PHPSESSID>
```

#### How to get your session ID:

1. Log in to the application
2. Open browser developer tools (F12)
3. Go to **Application** > **Cookies**
4. Copy the `PHPSESSID` value

#### What it tests:

**Public Endpoints:**
- Home page load (100 requests, 10 concurrent)
- Health check endpoint (500 requests, 50 concurrent)

**Authenticated Endpoints (if session provided):**
- Dashboard load (50 requests, 5 concurrent)
- Different date range filters (7/30/90 days)
- Stress test (100 requests, 20 concurrent)

#### Output:

```
╔════════════════════════════════════════════════════════════════════════════╗
║                         DASHBOARD LOAD TESTER                              ║
╚════════════════════════════════════════════════════════════════════════════╝

Configuration:
  Host: http://localhost:8000
  Session ID: abc123...

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
TEST 1: Home Page Load
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Requests per second:    512.34 [#/sec]
Time per request:       19.518 [ms] (mean)
...

╔════════════════════════════════════════════════════════════════════════════╗
║                          LOAD TESTING COMPLETE                             ║
╚════════════════════════════════════════════════════════════════════════════╝

Summary:
  ✓ Public endpoints tested
  ✓ Authenticated endpoints tested
  ✓ Multiple date ranges tested
  ✓ Stress test completed

Performance targets:
  • Home page: < 200ms
  • Dashboard (cold): < 2000ms
  • Dashboard (cached): < 300ms
  • Success rate: > 95%
```

---

## Performance Targets

| Metric | Target | How to Measure |
|--------|--------|----------------|
| Dashboard Initial Load | < 2000ms | `performance_profile.php` |
| Filter Update (cached) | < 300ms | `measure_filter_performance.php` |
| Home Page Load | < 200ms | `load_test.sh` |
| Success Rate | > 95% | `load_test.sh` |
| Memory Usage | < 50MB | `performance_profile.php` |

## Best Practices

### 1. Regular Profiling

Run performance tests regularly, especially:
- After implementing new features
- Before deploying to production
- When experiencing performance issues
- After updating dependencies

### 2. Baseline Measurements

Establish baseline performance metrics:

```bash
# 1. Profile initial load
./scripts/profile_dashboard.sh <token> > baseline_profile.txt

# 2. Measure filter performance
php scripts/measure_filter_performance.php <token> > baseline_filter.txt

# 3. Run load tests
./scripts/load_test.sh --session <session> > baseline_load.txt
```

### 3. Compare Results

After optimizations, compare new results against baseline:

```bash
# Run profiler again
./scripts/profile_dashboard.sh <token> > optimized_profile.txt

# Compare
diff baseline_profile.txt optimized_profile.txt
```

### 4. Continuous Monitoring

For production environments:
- Set up automated performance testing in CI/CD
- Monitor real user metrics (RUM)
- Use tools like New Relic, DataDog, or Prometheus
- Set up alerts for performance degradation

## Troubleshooting

### Issue: "Access token invalid or expired"

**Solution:** Get a fresh access token from your browser session.

### Issue: "Apache Bench (ab) not found"

**Solution:** Install apache2-utils/httpd-tools for your system (see Load Testing section).

### Issue: "Session ID not working"

**Solution:**
1. Make sure you're logged in
2. Check that the session hasn't expired
3. Try logging out and in again
4. Verify the PHPSESSID cookie value is correct

### Issue: "Rate limit errors during testing"

**Solution:**
1. Reduce the number of requests or concurrency
2. Wait a few minutes before retrying
3. Check Strava API rate limits in your account

## Additional Resources

- **Performance Documentation:** `docs/PERFORMANCE.md`
- **Installation Guide:** `docs/INSTALLATION.md`
- **Main README:** `README.md`

## Contributing

When adding new performance tests:
1. Follow the existing script structure
2. Document usage and output format
3. Update this README
4. Add examples to `docs/PERFORMANCE.md`

---

Last Updated: 2025-11-23
