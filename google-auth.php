<?php
declare(strict_types=1);

require_once __DIR__ . '/init.php';

if (isset($_GET['error'])) {
    error_log('Google OAuth returned error: ' . $_GET['error']);
    redirect('login?error=google_auth_cancelled');
}

if (isset($_GET['code'])) {
    $state = $_GET['state'] ?? '';
    $sessionState = $_SESSION['google_oauth_state'] ?? '';
    unset($_SESSION['google_oauth_state']);

    if (empty($state) || !hash_equals($sessionState, $state)) {
        error_log('Google OAuth state mismatch');
        redirect('login?error=invalid_state');
    }

    $code = (string) $_GET['code'];
    $profile = handle_google_oauth_callback($code);

    if (!$profile) {
        redirect('login?error=google_auth_failed');
    }

    $user = find_or_create_google_user($profile);

    if (!$user) {
        redirect('login?error=account_creation_failed');
    }

    login_user($user);
    redirect(get_dashboard_route($user['role']));
}

// Initiate Google OAuth Flow
$state = bin2hex(random_bytes(16));
$_SESSION['google_oauth_state'] = $state;

if (empty(GOOGLE_CLIENT_ID)) {
    redirect('login?error=google_not_configured');
}

$authUrl = get_google_auth_url($state);
redirect($authUrl);
