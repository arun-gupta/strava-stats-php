<?php
// Get error details from URL parameters or provide defaults
$errorType = $_GET['error'] ?? 'unknown';
$errorMessage = $_GET['message'] ?? null;

// Map error types to user-friendly messages
$errorMessages = [
    'authentication_required' => 'Please sign in to access this page.',
    'session_expired' => 'Your session has expired. Please sign in again.',
    'access_denied' => 'You denied access to your Strava data. The app needs permission to display your activities.',
    'invalid_state' => 'Security validation failed. Please try signing in again.',
    'missing_parameters' => 'Invalid OAuth response from Strava. Please try again.',
    'missing_verifier' => 'OAuth verification failed. Please try signing in again.',
    'token_exchange_failed' => 'Failed to exchange authorization code for access token. Please try again.',
    'network_error' => 'Network error occurred. Please check your connection and try again.',
    'api_error' => 'Strava API error. Please try again later.',
    'unknown' => 'An unexpected error occurred. Please try again.',
];

$title = '⚠️ Oops! Something Went Wrong';
$message = $errorMessage ?? ($errorMessages[$errorType] ?? $errorMessages['unknown']);

// Determine appropriate action based on error type
$showRetryButton = in_array($errorType, ['token_exchange_failed', 'network_error', 'api_error', 'unknown']);
$showSignInButton = in_array($errorType, ['authentication_required', 'session_expired', 'access_denied', 'invalid_state', 'missing_parameters', 'missing_verifier']);
?>

<div style="padding: 3rem 0; text-align: center;">
    <div style="max-width: 600px; margin: 0 auto; padding: 2rem; background: #fff3cd; border: 2px solid #ffc107; border-radius: 8px; box-shadow: 0 4px 12px rgba(255, 193, 7, 0.2);">
        <div style="font-size: 3rem; margin-bottom: 1rem;">⚠️</div>

        <h2 style="color: #856404; margin-top: 0; margin-bottom: 1rem;">
            Something Went Wrong
        </h2>

        <p style="color: #856404; font-size: 1.1rem; line-height: 1.6; margin: 1.5rem 0;">
            <?= htmlspecialchars($message) ?>
        </p>

        <?php if (in_array($errorType, ['network_error', 'api_error'])): ?>
            <div style="margin: 1.5rem 0; padding: 1rem; background: rgba(255, 255, 255, 0.7); border-radius: 6px; text-align: left;">
                <p style="margin: 0; color: #856404; font-size: 0.95rem;">
                    <strong>💡 Quick Tips:</strong><br>
                    • Check your internet connection<br>
                    • Wait a moment and try again<br>
                    • Visit <a href="https://status.strava.com" target="_blank" style="color: #fc4c02;">status.strava.com</a> to check if Strava is experiencing issues
                </p>
            </div>
        <?php endif; ?>

        <div style="margin-top: 2rem; display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
            <?php if ($showSignInButton): ?>
                <a href="/auth/strava"
                   style="display: inline-flex; align-items: center; gap: 8px; padding: 12px 24px;
                          background-color: #fc4c02; color: white; text-decoration: none;
                          border-radius: 4px; font-weight: 600; font-size: 16px; transition: all 0.2s ease;">
                    <svg width="20" height="20" viewBox="0 0 384 512" fill="currentColor">
                        <path d="M158.4 0L7 292h89.2l62.2-116.1L220.1 292h88.5zm150.2 292l-43.9 88.2-44.6-88.2h-67.6l112.2 220 111.5-220z"/>
                    </svg>
                    Connect with Strava
                </a>
            <?php endif; ?>

            <?php if ($showRetryButton): ?>
                <a href="javascript:history.back()"
                   style="padding: 12px 24px; background-color: #6c757d; color: white;
                          text-decoration: none; border-radius: 4px; font-weight: 600; font-size: 16px; transition: all 0.2s ease;">
                    ← Go Back
                </a>
            <?php endif; ?>

            <a href="/"
               style="padding: 12px 24px; background-color: #28a745; color: white;
                      text-decoration: none; border-radius: 4px; font-weight: 600; font-size: 16px; transition: all 0.2s ease;">
                🏠 Home
            </a>
        </div>
    </div>

    <?php if ($errorType !== 'unknown'): ?>
        <p style="margin-top: 2rem; color: #666; font-size: 0.9rem;">
            Error code: <code style="background: #f5f5f5; padding: 4px 8px; border-radius: 4px; font-family: monospace;"><?= htmlspecialchars($errorType) ?></code>
        </p>
    <?php endif; ?>
</div>
