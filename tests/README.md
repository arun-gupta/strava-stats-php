# Test Suite Documentation

## Overview

This test suite provides comprehensive coverage for the Strava Activity Analyzer application, focusing on unit tests for models and services.

## Test Structure

```
tests/
├── Unit/
│   ├── Models/
│   │   └── ActivityTest.php               # Tests for Activity model (21 tests)
│   └── Services/
│       └── ActivityServiceTest.php         # Tests for ActivityService (15 tests)
└── Integration/
    ├── OAuthFlowTest.php                   # OAuth 2.0 flow tests (14 tests)
    ├── ActivityFetchingTest.php            # API parsing tests (10 tests)
    ├── DashboardDataFlowTest.php           # Dashboard data flow (8 tests)
    └── RateLimitingTest.php                # Rate limiting tests (16 tests)
```

## Running Tests

### Run All Tests

```bash
./vendor/bin/phpunit
```

### Run with Test Documentation Output

```bash
./vendor/bin/phpunit --testdox
```

### Run Specific Test Suite

```bash
# Unit tests only
./vendor/bin/phpunit --testsuite Unit

# Integration tests only
./vendor/bin/phpunit --testsuite Integration
```

### Run Specific Test File

```bash
./vendor/bin/phpunit tests/Unit/Models/ActivityTest.php
```

### Run Specific Test Method

```bash
./vendor/bin/phpunit --filter testActivityCreation
```

## Code Coverage

### Prerequisites

To generate code coverage reports, you need to install a code coverage driver. We recommend **pcov** for its performance:

#### Install pcov (macOS with Homebrew)

```bash
pecl install pcov
```

Then add to your `php.ini`:

```ini
extension=pcov.so
pcov.enabled=1
```

#### Alternative: Install xdebug

```bash
pecl install xdebug
```

Then add to your `php.ini`:

```ini
zend_extension=xdebug.so
xdebug.mode=coverage
```

### Generate Coverage Reports

#### HTML Coverage Report (Recommended)

```bash
./vendor/bin/phpunit --coverage-html coverage/html
```

Then open `coverage/html/index.html` in your browser.

#### Text Coverage Summary

```bash
./vendor/bin/phpunit --coverage-text
```

#### Clover XML (for CI/CD)

```bash
./vendor/bin/phpunit --coverage-clover coverage/clover.xml
```

## Test Coverage Summary

### Current Test Statistics

- **Total Tests:** 84
- **Total Assertions:** 300
- **Test Status:** ✓ All Passing
- **Execution Time:** ~19ms
- **Memory Usage:** 10MB

### Coverage by Component

#### Unit Tests (36 tests)

| Component | Tests | Coverage |
|-----------|-------|----------|
| Activity Model | 21 tests | ~100% |
| ActivityService | 15 tests | ~100% |

#### Integration Tests (48 tests)

| Component | Tests | Description |
|-----------|-------|-------------|
| OAuth Flow | 14 tests | Complete OAuth 2.0 flow with PKCE |
| Activity Fetching | 10 tests | API response parsing and processing |
| Dashboard Data Flow | 8 tests | End-to-end data flow for all tabs |
| Rate Limiting | 16 tests | Middleware rate limiting logic |

### What's Tested

#### Activity Model (`tests/Unit/Models/ActivityTest.php`)

- ✓ Activity creation with all fields
- ✓ Creating from Strava API data (full and minimal)
- ✓ Distance conversions (km, miles)
- ✓ Time conversions (hours, formatted)
- ✓ Pace calculations (per km, per mile)
- ✓ Pace formatting (MM:SS)
- ✓ Edge cases (zero distance, zero time)
- ✓ Activity type checks (isRun, isRide)
- ✓ Array conversion
- ✓ Handling optional/null fields

#### ActivityService (`tests/Unit/Services/ActivityServiceTest.php`)

- ✓ Grouping activities by type
- ✓ Counting activities by type
- ✓ Calculating total moving time by type
- ✓ Calculating total distance by type
- ✓ Correct sorting (descending)
- ✓ Empty array handling
- ✓ Single type handling
- ✓ Correct accumulation of values
- ✓ Activity preservation in grouping

#### OAuth Flow (`tests/Integration/OAuthFlowTest.php`)

- ✓ Authorization URL generation with PKCE
- ✓ State parameter validation (CSRF protection)
- ✓ Code verifier and challenge generation
- ✓ Callback handling (valid/invalid/missing parameters)
- ✓ Token exchange request format
- ✓ Token response handling and storage
- ✓ Session regeneration after login
- ✓ OAuth state cleanup
- ✓ Sign out flow

#### Activity Fetching (`tests/Integration/ActivityFetchingTest.php`)

- ✓ Parse multiple activities from API response
- ✓ Group activities by type
- ✓ Calculate activity statistics (counts, distance, time)
- ✓ Handle empty/incomplete API responses
- ✓ Filter activities by date range
- ✓ Calculate running-specific statistics
- ✓ Handle multiple activity types
- ✓ Handle paginated API responses
- ✓ Activity data integrity

#### Dashboard Data Flow (`tests/Integration/DashboardDataFlowTest.php`)

- ✓ Complete data flow for Overview tab
- ✓ Complete data flow for Duration tab
- ✓ Complete data flow for Running tab
- ✓ Complete data flow for Heatmap tab
- ✓ Complete data flow for Trends tab
- ✓ Date range filtering (7/30/90 days)
- ✓ Caching simulation
- ✓ Distance histogram generation

#### Rate Limiting (`tests/Integration/RateLimitingTest.php`)

- ✓ Initialize rate limit tracking
- ✓ Increment request counts
- ✓ Rate limit enforcement (exceeded/not exceeded)
- ✓ Rate limit window expiration
- ✓ Different paths have independent limits
- ✓ Different IPs have independent limits
- ✓ Protected vs unprotected paths
- ✓ Calculate time until reset
- ✓ Generate rate limit headers
- ✓ Handle concurrent requests
- ✓ Handle burst requests

### What's Not Tested Yet

- TokenRefreshService (requires HTTP client mocking - complex)
- StravaClient live API calls (requires API mocking - covered by integration tests)
- Controllers request/response (covered by integration tests)
- Middleware in Slim framework context (logic covered by integration tests)

## Writing Tests

### Test Naming Convention

- Test classes: `{ClassName}Test.php`
- Test methods: `test{MethodName}{Scenario}()`
- Use descriptive names: `testGetPacePerKmReturnsNullForZeroDistance`

### Example Test Structure

```php
<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;

class MyServiceTest extends TestCase
{
    private MyService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new MyService();
    }

    public function testMethodNameDoesExpectedBehavior(): void
    {
        // Arrange
        $input = 'test data';

        // Act
        $result = $this->service->method($input);

        // Assert
        $this->assertSame('expected', $result);
    }

    public function testMethodNameHandlesEdgeCase(): void
    {
        // Test edge cases
        $this->assertNull($this->service->method(null));
    }
}
```

### Best Practices

1. **Arrange-Act-Assert Pattern**
   - Arrange: Set up test data
   - Act: Execute the method
   - Assert: Verify the result

2. **Test One Thing Per Test**
   - Each test should verify one behavior
   - Use descriptive test names

3. **Use Data Providers for Multiple Cases**
   ```php
   /**
    * @dataProvider distanceProvider
    */
   public function testDistance(float $meters, float $expectedKm): void
   {
       $activity = new Activity(..., distance: $meters, ...);
       $this->assertSame($expectedKm, $activity->getDistanceKm());
   }

   public function distanceProvider(): array
   {
       return [
           [1000.0, 1.0],
           [5000.0, 5.0],
           [10000.0, 10.0],
       ];
   }
   ```

4. **Test Edge Cases**
   - Null values
   - Empty arrays
   - Zero values
   - Boundary conditions

5. **Use Helper Methods**
   - Extract common test setup into helper methods
   - See `createTestActivity()` in ActivityServiceTest

## Continuous Integration

### GitHub Actions Example

Create `.github/workflows/tests.yml`:

```yaml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest

    steps:
      - uses: actions/checkout@v3

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
          extensions: mbstring, pcov
          coverage: pcov

      - name: Install Dependencies
        run: composer install --prefer-dist --no-progress

      - name: Run Tests
        run: ./vendor/bin/phpunit --coverage-clover coverage.xml

      - name: Upload Coverage to Codecov
        uses: codecov/codecov-action@v3
        with:
          file: ./coverage.xml
```

### GitLab CI Example

Create `.gitlab-ci.yml`:

```yaml
test:
  image: php:8.3
  before_script:
    - apt-get update && apt-get install -y git zip unzip
    - curl -sS https://getcomposer.org/installer | php
    - php composer.phar install
    - pecl install pcov && docker-php-ext-enable pcov
  script:
    - ./vendor/bin/phpunit --coverage-text --colors=never
  coverage: '/^\s*Lines:\s*\d+.\d+\%/'
```

## Test Performance

### Current Performance

- **Execution Time:** ~12ms
- **Memory Usage:** ~10MB
- **Tests per Second:** ~3,000

### Performance Tips

1. Avoid database connections in unit tests
2. Mock external dependencies
3. Use setUp() and tearDown() efficiently
4. Run slow tests separately with `@group` annotations

```php
/**
 * @group slow
 * @group integration
 */
public function testSlowOperation(): void
{
    // Slow test
}
```

Then run fast tests only:

```bash
./vendor/bin/phpunit --exclude-group slow
```

## Troubleshooting

### Problem: "No code coverage driver available"

**Solution:** Install pcov or xdebug (see Code Coverage section above)

### Problem: "Class not found"

**Solution:** Run `composer dump-autoload`

### Problem: Tests fail with session errors

**Solution:** Tests should not rely on session. Mock session data instead.

### Problem: Tests are slow

**Solution:**
1. Use `--exclude-group slow` to skip slow tests
2. Use `--order-by=defects` to run failing tests first
3. Reduce database/API interactions

## Contributing

When adding new features:

1. Write tests first (TDD approach)
2. Ensure all tests pass: `./vendor/bin/phpunit`
3. Check coverage: `./vendor/bin/phpunit --coverage-text`
4. Aim for >80% coverage for new code
5. Update this README if adding new test patterns

## Resources

- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [Testing Best Practices](https://phptherightway.com/#testing)
- [Test-Driven Development](https://martinfowler.com/bliki/TestDrivenDevelopment.html)

---

Last Updated: 2025-11-23
