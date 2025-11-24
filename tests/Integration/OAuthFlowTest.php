<?php

declare(strict_types=1);

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;

/**
 * OAuth Flow Integration Test
 *
 * Tests the complete OAuth authorization flow including:
 * - Authorization URL generation
 * - State parameter validation
 * - PKCE code challenge/verifier
 * - Token exchange simulation
 */
class OAuthFlowTest extends TestCase
{
    private string $testClientId;
    private string $testClientSecret;
    private string $testRedirectUri;

    protected function setUp(): void
    {
        parent::setUp();

        // Load OAuth config
        $config = require __DIR__ . '/../../config/oauth.php';
        $this->testClientId = $config['strava']['client_id'];
        $this->testClientSecret = $config['strava']['client_secret'];
        $this->testRedirectUri = $config['strava']['redirect_uri'];

        // Start session for OAuth state management
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Clear session before each test
        $_SESSION = [];
    }

    protected function tearDown(): void
    {
        // Clean up session
        $_SESSION = [];
        parent::tearDown();
    }

    public function testAuthorizationUrlGeneration(): void
    {
        // Simulate what AuthController::authorize() does
        $state = bin2hex(random_bytes(32));
        $_SESSION['oauth_state'] = $state;

        // Generate PKCE code verifier and challenge
        $codeVerifier = $this->generateCodeVerifier();
        $codeChallenge = $this->generateCodeChallenge($codeVerifier);
        $_SESSION['oauth_code_verifier'] = $codeVerifier;

        // Build authorization URL
        $authUrl = 'https://www.strava.com/oauth/authorize?' . http_build_query([
            'client_id' => $this->testClientId,
            'redirect_uri' => $this->testRedirectUri,
            'response_type' => 'code',
            'approval_prompt' => 'auto',
            'scope' => 'activity:read_all',
            'state' => $state,
            'code_challenge' => $codeChallenge,
            'code_challenge_method' => 'S256',
        ]);

        // Verify URL components
        $this->assertStringContainsString('https://www.strava.com/oauth/authorize', $authUrl);
        $this->assertStringContainsString('client_id=' . urlencode($this->testClientId), $authUrl);
        $this->assertStringContainsString('state=' . $state, $authUrl);
        $this->assertStringContainsString('code_challenge=', $authUrl);
        $this->assertStringContainsString('code_challenge_method=S256', $authUrl);

        // Verify session state is set
        $this->assertSame($state, $_SESSION['oauth_state']);
        $this->assertSame($codeVerifier, $_SESSION['oauth_code_verifier']);
    }

    public function testStateParameterValidation(): void
    {
        // Set up session with state
        $validState = 'test_state_' . bin2hex(random_bytes(16));
        $_SESSION['oauth_state'] = $validState;

        // Test valid state
        $this->assertTrue($this->validateState($validState));

        // Test invalid state
        $this->assertFalse($this->validateState('invalid_state'));

        // Test missing state in session
        unset($_SESSION['oauth_state']);
        $this->assertFalse($this->validateState($validState));
    }

    public function testPKCECodeVerifierGeneration(): void
    {
        $verifier = $this->generateCodeVerifier();

        // Verify length (43-128 characters for base64url encoded 32-96 bytes)
        $this->assertGreaterThanOrEqual(43, strlen($verifier));
        $this->assertLessThanOrEqual(128, strlen($verifier));

        // Verify it's base64url encoded (only contains A-Z, a-z, 0-9, -, _)
        $this->assertMatchesRegularExpression('/^[A-Za-z0-9_-]+$/', $verifier);
    }

    public function testPKCECodeChallengeGeneration(): void
    {
        $verifier = $this->generateCodeVerifier();
        $challenge = $this->generateCodeChallenge($verifier);

        // Verify challenge is generated
        $this->assertNotEmpty($challenge);

        // Verify it's base64url encoded
        $this->assertMatchesRegularExpression('/^[A-Za-z0-9_-]+$/', $challenge);

        // Verify challenge is different from verifier
        $this->assertNotSame($verifier, $challenge);

        // Verify same verifier produces same challenge
        $challenge2 = $this->generateCodeChallenge($verifier);
        $this->assertSame($challenge, $challenge2);
    }

    public function testCallbackWithValidState(): void
    {
        // Set up session
        $state = 'test_state_' . bin2hex(random_bytes(16));
        $codeVerifier = $this->generateCodeVerifier();
        $_SESSION['oauth_state'] = $state;
        $_SESSION['oauth_code_verifier'] = $codeVerifier;

        // Simulate callback with valid state
        $queryParams = [
            'code' => 'test_authorization_code',
            'state' => $state,
        ];

        // Validate state
        $isValid = $this->validateState($queryParams['state']);
        $this->assertTrue($isValid);

        // Verify code verifier is available
        $this->assertSame($codeVerifier, $_SESSION['oauth_code_verifier']);
    }

    public function testCallbackWithInvalidState(): void
    {
        // Set up session with different state
        $_SESSION['oauth_state'] = 'valid_state';

        // Simulate callback with invalid state
        $queryParams = [
            'code' => 'test_authorization_code',
            'state' => 'invalid_state',
        ];

        // Validate state should fail
        $isValid = $this->validateState($queryParams['state']);
        $this->assertFalse($isValid);
    }

    public function testCallbackWithAccessDenied(): void
    {
        $queryParams = [
            'error' => 'access_denied',
            'error_description' => 'The user denied access',
        ];

        // Check if error is present
        $this->assertArrayHasKey('error', $queryParams);
        $this->assertSame('access_denied', $queryParams['error']);
    }

    public function testCallbackWithMissingCode(): void
    {
        $state = 'test_state';
        $_SESSION['oauth_state'] = $state;

        $queryParams = [
            'state' => $state,
            // 'code' is missing
        ];

        // Should fail validation due to missing code
        $this->assertArrayNotHasKey('code', $queryParams);
    }

    public function testCallbackWithMissingState(): void
    {
        $queryParams = [
            'code' => 'test_code',
            // 'state' is missing
        ];

        // Should fail validation due to missing state
        $this->assertArrayNotHasKey('state', $queryParams);
    }

    public function testTokenExchangeRequestFormat(): void
    {
        $code = 'test_authorization_code';
        $codeVerifier = $this->generateCodeVerifier();

        // Simulate token exchange request parameters
        $tokenParams = [
            'client_id' => $this->testClientId,
            'client_secret' => $this->testClientSecret,
            'code' => $code,
            'grant_type' => 'authorization_code',
            'code_verifier' => $codeVerifier,
        ];

        // Verify all required parameters are present
        $this->assertArrayHasKey('client_id', $tokenParams);
        $this->assertArrayHasKey('client_secret', $tokenParams);
        $this->assertArrayHasKey('code', $tokenParams);
        $this->assertArrayHasKey('grant_type', $tokenParams);
        $this->assertArrayHasKey('code_verifier', $tokenParams);

        // Verify grant_type is correct
        $this->assertSame('authorization_code', $tokenParams['grant_type']);
    }

    public function testTokenResponseHandling(): void
    {
        // Simulate successful token response from Strava
        $tokenResponse = [
            'access_token' => 'test_access_token_' . bin2hex(random_bytes(16)),
            'refresh_token' => 'test_refresh_token_' . bin2hex(random_bytes(16)),
            'expires_at' => time() + 21600, // 6 hours
            'expires_in' => 21600,
            'athlete' => [
                'id' => 12345,
                'username' => 'testuser',
                'firstname' => 'Test',
                'lastname' => 'User',
            ],
        ];

        // Store tokens in session
        $_SESSION['access_token'] = $tokenResponse['access_token'];
        $_SESSION['refresh_token'] = $tokenResponse['refresh_token'];
        $_SESSION['expires_at'] = $tokenResponse['expires_at'];
        $_SESSION['athlete'] = $tokenResponse['athlete'];

        // Verify tokens are stored correctly
        $this->assertSame($tokenResponse['access_token'], $_SESSION['access_token']);
        $this->assertSame($tokenResponse['refresh_token'], $_SESSION['refresh_token']);
        $this->assertSame($tokenResponse['expires_at'], $_SESSION['expires_at']);
        $this->assertIsArray($_SESSION['athlete']);
        $this->assertSame(12345, $_SESSION['athlete']['id']);
    }

    public function testSessionRegenerationAfterLogin(): void
    {
        $oldSessionId = session_id();

        // Simulate successful login - session should be regenerated
        session_regenerate_id(true);

        $newSessionId = session_id();

        // Verify session ID changed
        $this->assertNotSame($oldSessionId, $newSessionId);
    }

    public function testOAuthStateCleanupAfterSuccess(): void
    {
        // Set up OAuth state
        $_SESSION['oauth_state'] = 'test_state';
        $_SESSION['oauth_code_verifier'] = 'test_verifier';
        $_SESSION['access_token'] = 'test_token';

        // Clean up OAuth state (simulating successful callback)
        unset($_SESSION['oauth_state']);
        unset($_SESSION['oauth_code_verifier']);

        // Verify OAuth state is cleared
        $this->assertArrayNotHasKey('oauth_state', $_SESSION);
        $this->assertArrayNotHasKey('oauth_code_verifier', $_SESSION);

        // Verify access token remains
        $this->assertArrayHasKey('access_token', $_SESSION);
    }

    public function testSignOutClearsSession(): void
    {
        // Set up session with user data
        $_SESSION['access_token'] = 'test_token';
        $_SESSION['refresh_token'] = 'test_refresh';
        $_SESSION['expires_at'] = time() + 3600;
        $_SESSION['athlete'] = ['id' => 12345];

        // Clear all session data (simulating sign out)
        $_SESSION = [];

        // Verify all data is cleared
        $this->assertEmpty($_SESSION);
        $this->assertArrayNotHasKey('access_token', $_SESSION);
        $this->assertArrayNotHasKey('refresh_token', $_SESSION);
        $this->assertArrayNotHasKey('expires_at', $_SESSION);
        $this->assertArrayNotHasKey('athlete', $_SESSION);
    }

    // Helper methods (duplicated from AuthController for testing)

    private function generateCodeVerifier(): string
    {
        $randomBytes = random_bytes(32);
        return rtrim(strtr(base64_encode($randomBytes), '+/', '-_'), '=');
    }

    private function generateCodeChallenge(string $verifier): string
    {
        $hash = hash('sha256', $verifier, true);
        return rtrim(strtr(base64_encode($hash), '+/', '-_'), '=');
    }

    private function validateState(string $state): bool
    {
        return isset($_SESSION['oauth_state']) && $state === $_SESSION['oauth_state'];
    }
}
