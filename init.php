<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/vendor/autoload.php';

function start_secure_session(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        $host = $_SERVER['HTTP_HOST'] ?? '';
        $domain = '';
        if ($host !== '' && !str_contains($host, 'localhost') && !str_contains($host, '127.0.0.1')) {
            $domain = explode(':', $host)[0];
        }

        $cookieParams = [
            'lifetime' => 0,
            'path' => '/',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ];
        if (!empty($domain)) {
            $cookieParams['domain'] = $domain;
        }

        session_set_cookie_params($cookieParams);
        session_start();
    }
}

function send_security_headers(): void
{
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
}

function db_connect(): PDO
{
    $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', DB_HOST, DB_NAME, DB_CHARSET);
    return new PDO($dsn, DB_USER, DB_PASS, PDO_OPTIONS);
}

function generate_csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_token(): string
{
    return generate_csrf_token();
}

function csrf_input(): string
{
    return '<input type="hidden" name="csrf_token" value="' . esc(csrf_token()) . '">';
}

function validate_csrf_token(?string $token): bool
{
    return is_string($token)
        && !empty($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST';
}

function esc(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function sanitize_text(string $value): string
{
    return trim(filter_var($value, FILTER_SANITIZE_FULL_SPECIAL_CHARS));
}

function sanitize_email(string $value): string
{
    return trim(filter_var($value, FILTER_SANITIZE_EMAIL));
}

function sanitize_int($value): int
{
    return filter_var((string)$value, FILTER_VALIDATE_INT, ['options' => ['default' => 0]]);
}

function old(string $key): string
{
    return esc($_POST[$key] ?? '');
}

function current_url(): string
{
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
    return sprintf('%s://%s%s', $scheme, $host, $requestUri);
}

function is_active_nav(string $href): bool
{
    $current = pathinfo($_SERVER['SCRIPT_NAME'] ?? '', PATHINFO_FILENAME);
    $target = pathinfo(parse_url($href, PHP_URL_PATH), PATHINFO_FILENAME);
    return $current === $target;
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function generate_captcha_question(): string
{
    $a = random_int(3, 9);
    $b = random_int(1, 9);
    $_SESSION['captcha_answer'] = $a + $b;
    return sprintf('What is %d + %d?', $a, $b);
}

function verify_captcha_answer(?string $value): bool
{
    if (!isset($_SESSION['captcha_answer'])) {
        return false;
    }
    return (int) trim($value) === $_SESSION['captcha_answer'];
}

function login_user(array $user): void
{
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_role'] = $user['role'];
}

function current_user(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    return [
        'id' => (int) $_SESSION['user_id'],
        'name' => (string) ($_SESSION['user_name'] ?? ''),
        'email' => (string) ($_SESSION['user_email'] ?? ''),
        'role' => (string) ($_SESSION['user_role'] ?? ''),
    ];
}

function is_logged_in(): bool
{
    return current_user() !== null;
}

function is_admin(): bool
{
    $user = current_user();
    return $user !== null && $user['role'] === 'admin';
}

function require_login(string $redirect = 'login'): void
{
    if (!is_logged_in()) {
        redirect($redirect);
    }
}

function require_admin(string $redirect = 'login'): void
{
    if (!is_admin()) {
        redirect($redirect);
    }
}

function logout_user(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}

function hash_password(string $password): string
{
    return password_hash($password, PASSWORD_DEFAULT);
}

function verify_password(string $password, string $hash): bool
{
    return password_verify($password, $hash);
}

function get_user_by_email(string $email): ?array
{
    try {
        $db = db_connect();
        $stmt = $db->prepare('SELECT id, name, email, role FROM users WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);
        return $stmt->fetch() ?: null;
    } catch (Throwable $e) {
        error_log('get_user_by_email failed: ' . $e->getMessage());
        return null;
    }
}

function set_password_reset_token(int $userId, string $token, string $expiresAt): bool
{
    try {
        $db = db_connect();
        $stmt = $db->prepare('UPDATE users SET reset_token = :token, reset_token_expires_at = :expires_at WHERE id = :id');
        return $stmt->execute([
            ':token' => $token,
            ':expires_at' => $expiresAt,
            ':id' => $userId,
        ]);
    } catch (Throwable $e) {
        error_log('set_password_reset_token failed: ' . $e->getMessage());
        return false;
    }
}

function get_user_by_reset_token(string $token): ?array
{
    try {
        $db = db_connect();
        $stmt = $db->prepare('SELECT id, name, email FROM users WHERE reset_token = :token AND reset_token_expires_at > NOW() LIMIT 1');
        $stmt->execute([':token' => $token]);
        return $stmt->fetch() ?: null;
    } catch (Throwable $e) {
        error_log('get_user_by_reset_token failed: ' . $e->getMessage());
        return null;
    }
}

function clear_password_reset_token(int $userId): bool
{
    try {
        $db = db_connect();
        $stmt = $db->prepare('UPDATE users SET reset_token = NULL, reset_token_expires_at = NULL WHERE id = :id');
        return $stmt->execute([':id' => $userId]);
    } catch (Throwable $e) {
        error_log('clear_password_reset_token failed: ' . $e->getMessage());
        return false;
    }
}

function update_user_password(int $userId, string $newPassword): bool
{
    try {
        $db = db_connect();
        $stmt = $db->prepare('UPDATE users SET password_hash = :password_hash WHERE id = :id');
        return $stmt->execute([
            ':password_hash' => hash_password($newPassword),
            ':id' => $userId,
        ]);
    } catch (Throwable $e) {
        error_log('update_user_password failed: ' . $e->getMessage());
        return false;
    }
}

function get_user_donations(string $email): array
{
    try {
        $db = db_connect();
        $stmt = $db->prepare('SELECT * FROM donations WHERE email = :email ORDER BY created_at DESC');
        $stmt->execute([':email' => $email]);
        return $stmt->fetchAll();
    } catch (Throwable $e) {
        error_log('get_user_donations failed: ' . $e->getMessage());
        return [];
    }
}

function get_user_applications(string $email): array
{
    try {
        $db = db_connect();
        $stmt = $db->prepare('SELECT * FROM applications WHERE email = :email ORDER BY created_at DESC');
        $stmt->execute([':email' => $email]);
        return $stmt->fetchAll();
    } catch (Throwable $e) {
        error_log('get_user_applications failed: ' . $e->getMessage());
        return [];
    }
}

function get_all_applications(): array
{
    try {
        $db = db_connect();
        $stmt = $db->query('SELECT * FROM applications ORDER BY created_at DESC');
        return $stmt->fetchAll();
    } catch (Throwable $e) {
        error_log('get_all_applications failed: ' . $e->getMessage());
        return [];
    }
}

function get_application_by_id(int $id): ?array
{
    try {
        $db = db_connect();
        $stmt = $db->prepare('SELECT * FROM applications WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    } catch (Throwable $e) {
        error_log('get_application_by_id failed: ' . $e->getMessage());
        return null;
    }
}

function send_email(array $options): bool
{
    $mailer = new PHPMailer\PHPMailer\PHPMailer(true);

    try {
        $mailer->isSMTP();
        $mailer->Host = SMTP_HOST;
        $mailer->SMTPAuth = true;
        $mailer->Username = SMTP_USERNAME;
        $mailer->Password = SMTP_PASSWORD;
        $mailer->SMTPSecure = SMTP_SECURE;
        $mailer->Port = (int) SMTP_PORT;

        $mailer->setFrom(EMAIL_FROM, EMAIL_FROM_NAME);
        $mailer->addAddress($options['to'], $options['to_name'] ?? '');

        if (!empty($options['reply_to'])) {
            $mailer->addReplyTo($options['reply_to']);
        }

        $mailer->Subject = $options['subject'];
        $mailer->CharSet = 'UTF-8';
        $mailer->isHTML(true);

        $htmlBody = $options['body'];
        if (!preg_match('/<\s*html/i', $htmlBody)) {
            $htmlBody = '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"></head><body>' . $htmlBody . '</body></html>';
        }

        $mailer->Body = $htmlBody;
        if (!empty($options['alt_body'])) {
            $mailer->AltBody = $options['alt_body'];
        }

        return $mailer->send();
    } catch (Throwable $e) {
        error_log('send_email failed: ' . $e->getMessage());
        return false;
    }
}

function application_exists(string $email): bool
{
    try {
        $db = db_connect();
        $stmt = $db->prepare('SELECT id FROM applications WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);
        return (bool) $stmt->fetchColumn();
    } catch (Throwable $e) {
        error_log('application_exists failed: ' . $e->getMessage());
        return false;
    }
}

function get_all_donations(): array
{
    try {
        $db = db_connect();
        $stmt = $db->query('SELECT * FROM donations ORDER BY created_at DESC');
        return $stmt->fetchAll();
    } catch (Throwable $e) {
        error_log('get_all_donations failed: ' . $e->getMessage());
        return [];
    }
}

function get_donation_by_id(int $id): ?array
{
    try {
        $db = db_connect();
        $stmt = $db->prepare('SELECT * FROM donations WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    } catch (Throwable $e) {
        error_log('get_donation_by_id failed: ' . $e->getMessage());
        return null;
    }
}

function get_all_contacts(): array
{
    try {
        $db = db_connect();
        $stmt = $db->query('SELECT * FROM contact_messages ORDER BY created_at DESC');
        return $stmt->fetchAll();
    } catch (Throwable $e) {
        error_log('get_all_contacts failed: ' . $e->getMessage());
        return [];
    }
}

function get_contact_by_id(int $id): ?array
{
    try {
        $db = db_connect();
        $stmt = $db->prepare('SELECT * FROM contact_messages WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    } catch (Throwable $e) {
        error_log('get_contact_by_id failed: ' . $e->getMessage());
        return null;
    }
}

function get_all_partners(): array
{
    try {
        $db = db_connect();
        $stmt = $db->query('SELECT * FROM partners ORDER BY created_at DESC');
        return $stmt->fetchAll();
    } catch (Throwable $e) {
        error_log('get_all_partners failed: ' . $e->getMessage());
        return [];
    }
}

function get_partner_by_id(int $id): ?array
{
    try {
        $db = db_connect();
        $stmt = $db->prepare('SELECT * FROM partners WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    } catch (Throwable $e) {
        error_log('get_partner_by_id failed: ' . $e->getMessage());
        return null;
    }
}

function partner_request_exists(string $email, string $organisationName): bool
{
    try {
        $db = db_connect();
        $stmt = $db->prepare('SELECT id FROM partners WHERE email = :email AND organisation_name = :organisation_name LIMIT 1');
        $stmt->execute([
            ':email' => $email,
            ':organisation_name' => $organisationName,
        ]);
        return (bool) $stmt->fetchColumn();
    } catch (Throwable $e) {
        error_log('partner_request_exists failed: ' . $e->getMessage());
        return false;
    }
}

function delete_application_by_id(int $id): bool
{
    try {
        $db = db_connect();
        $stmt = $db->prepare('DELETE FROM applications WHERE id = :id LIMIT 1');
        return $stmt->execute([':id' => $id]);
    } catch (Throwable $e) {
        error_log('delete_application_by_id failed: ' . $e->getMessage());
        return false;
    }
}

function delete_donation_by_id(int $id): bool
{
    try {
        $db = db_connect();
        $stmt = $db->prepare('DELETE FROM donations WHERE id = :id LIMIT 1');
        return $stmt->execute([':id' => $id]);
    } catch (Throwable $e) {
        error_log('delete_donation_by_id failed: ' . $e->getMessage());
        return false;
    }
}

function delete_contact_by_id(int $id): bool
{
    try {
        $db = db_connect();
        $stmt = $db->prepare('DELETE FROM contact_messages WHERE id = :id LIMIT 1');
        return $stmt->execute([':id' => $id]);
    } catch (Throwable $e) {
        error_log('delete_contact_by_id failed: ' . $e->getMessage());
        return false;
    }
}

function delete_partner_by_id(int $id): bool
{
    try {
        $db = db_connect();
        $stmt = $db->prepare('DELETE FROM partners WHERE id = :id LIMIT 1');
        return $stmt->execute([':id' => $id]);
    } catch (Throwable $e) {
        error_log('delete_partner_by_id failed: ' . $e->getMessage());
        return false;
    }
}

function parse_application_reference(string $reference): ?int
{
    if (preg_match('/APP-\d{4}-(\d{1,5})/i', $reference, $matches)) {
        return (int) $matches[1];
    }
    return null;
}

function get_application_by_reference(string $reference): ?array
{
    $id = parse_application_reference($reference);
    if ($id < 1) {
        return null;
    }
    try {
        $db = db_connect();
        $stmt = $db->prepare('SELECT * FROM applications WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    } catch (Throwable $e) {
        error_log('get_application_by_reference failed: ' . $e->getMessage());
        return null;
    }
}

function parse_device_reference(string $reference): ?int
{
    if (preg_match('/DEV-(\d{1,5})/i', $reference, $matches)) {
        return (int) $matches[1];
    }
    return null;
}

function get_donation_by_reference(string $reference): ?array
{
    $id = parse_device_reference($reference);
    if ($id < 1) {
        return null;
    }
    try {
        $db = db_connect();
        $stmt = $db->prepare('SELECT * FROM donations WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    } catch (Throwable $e) {
        error_log('get_donation_by_reference failed: ' . $e->getMessage());
        return null;
    }
}

function get_donation_timeline(array $donation): array
{
    $createdAt = new DateTime($donation['created_at']);
    $ageDays = (new DateTime())->diff($createdAt)->days;
    $stage = 'Refurbishing';
    if ($ageDays >= 21) {
        $stage = 'Delivered';
    } elseif ($ageDays >= 10) {
        $stage = 'Quality assured';
    }

    return [
        ['label' => 'Received', 'date' => $createdAt->format('Y-m-d'), 'complete' => true],
        ['label' => 'Refurbished', 'date' => $createdAt->modify('+5 days')->format('Y-m-d'), 'complete' => $ageDays >= 5],
        ['label' => 'Quality assured', 'date' => $createdAt->modify('+5 days')->format('Y-m-d'), 'complete' => $ageDays >= 10],
        ['label' => 'Delivered', 'date' => $createdAt->modify('+10 days')->format('Y-m-d'), 'complete' => $ageDays >= 21],
    ];
}

function get_dashboard_route(string $role): string
{
    return match ($role) {
        'admin' => 'admin-dashboard',
        'donor' => 'donor-dashboard',
        'applicant' => 'applicant-dashboard',
        default => 'index',
    };
}

function get_google_auth_url(string $state): string
{
    $params = [
        'client_id' => GOOGLE_CLIENT_ID,
        'redirect_uri' => GOOGLE_REDIRECT_URI,
        'response_type' => 'code',
        'scope' => 'openid email profile',
        'state' => $state,
        'prompt' => 'select_account',
    ];
    return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
}

function handle_google_oauth_callback(string $code): ?array
{
    if (empty(GOOGLE_CLIENT_ID) || empty(GOOGLE_CLIENT_SECRET)) {
        error_log('Google OAuth credentials not configured.');
        return null;
    }

    $tokenUrl = 'https://oauth2.googleapis.com/token';
    $postFields = http_build_query([
        'code' => $code,
        'client_id' => GOOGLE_CLIENT_ID,
        'client_secret' => GOOGLE_CLIENT_SECRET,
        'redirect_uri' => GOOGLE_REDIRECT_URI,
        'grant_type' => 'authorization_code',
    ]);

    $ch = curl_init($tokenUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200 || !$response) {
        error_log('Google OAuth token exchange failed: ' . ($response ?: 'No response'));
        return null;
    }

    $tokenData = json_decode($response, true);
    $accessToken = $tokenData['access_token'] ?? null;
    if (!$accessToken) {
        return null;
    }

    $userInfoUrl = 'https://www.googleapis.com/oauth2/v3/userinfo';
    $ch = curl_init($userInfoUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $accessToken]);
    $userResponse = curl_exec($ch);
    $userHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($userHttpCode !== 200 || !$userResponse) {
        error_log('Google OAuth userinfo request failed');
        return null;
    }

    return json_decode($userResponse, true);
}

function find_or_create_google_user(array $profile): ?array
{
    $email = filter_var($profile['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $googleId = (string)($profile['sub'] ?? '');
    $name = trim($profile['name'] ?? $profile['given_name'] ?? 'Google User');

    if (!$email) {
        return null;
    }

    try {
        $db = db_connect();

        $stmt = $db->prepare('SELECT id, name, email, password_hash, role, google_id FROM users WHERE google_id = :google_id OR email = :email LIMIT 1');
        $stmt->execute([
            ':google_id' => $googleId,
            ':email' => $email,
        ]);
        $user = $stmt->fetch();

        if ($user) {
            if (empty($user['google_id']) && $googleId !== '') {
                $upStmt = $db->prepare('UPDATE users SET google_id = :google_id WHERE id = :id');
                $upStmt->execute([':google_id' => $googleId, ':id' => $user['id']]);
                $user['google_id'] = $googleId;
            }
            return $user;
        }

        $passwordHash = hash_password(bin2hex(random_bytes(16)));
        $insertStmt = $db->prepare('INSERT INTO users (name, email, password_hash, role, google_id, created_at, updated_at) VALUES (:name, :email, :password_hash, :role, :google_id, NOW(), NOW())');
        $insertStmt->execute([
            ':name' => $name,
            ':email' => $email,
            ':password_hash' => $passwordHash,
            ':role' => 'applicant',
            ':google_id' => $googleId,
        ]);

        $newId = (int)$db->lastInsertId();
        return [
            'id' => $newId,
            'name' => $name,
            'email' => $email,
            'role' => 'applicant',
            'google_id' => $googleId,
        ];
    } catch (Throwable $e) {
        error_log('find_or_create_google_user error: ' . $e->getMessage());
        return null;
    }
}

function provision_user_account_if_needed(string $email, string $fullName, string $role = 'applicant'): array
{
    $email = sanitize_email($email);
    $fullName = sanitize_text($fullName);

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['user' => null, 'created' => false, 'temp_password' => null];
    }

    $existingUser = get_user_by_email($email);
    if ($existingUser) {
        if (!is_logged_in()) {
            login_user($existingUser);
        }
        return ['user' => $existingUser, 'created' => false, 'temp_password' => null];
    }

    $tempPassword = bin2hex(random_bytes(4));
    $passwordHash = hash_password($tempPassword);

    try {
        $db = db_connect();
        $stmt = $db->prepare('INSERT INTO users (name, email, password_hash, role, created_at, updated_at) VALUES (:name, :email, :password_hash, :role, NOW(), NOW())');
        $stmt->execute([
            ':name' => $fullName,
            ':email' => $email,
            ':password_hash' => $passwordHash,
            ':role' => $role,
        ]);

        $newUserId = (int)$db->lastInsertId();
        $newUser = [
            'id' => $newUserId,
            'name' => $fullName,
            'email' => $email,
            'role' => $role,
        ];

        $loginUrl = SITE_URL . '/login';
        $emailBody = sprintf(
            '<p>Hi %s,</p>' .
            '<p>An account has been automatically created for you at Zee Tech Foundation so you can track your request status from your dashboard.</p>' .
            '<p><strong>Your Account Login Credentials:</strong></p>' .
            '<ul>' .
            '<li><strong>Email:</strong> %s</li>' .
            '<li><strong>Temporary Password:</strong> <code>%s</code></li>' .
            '</ul>' .
            '<p>You can <a href="%s">Sign in here</a> anytime to access your dashboard. We recommend changing your password after signing in.</p>' .
            '<p>Best regards,<br>Zee Tech Foundation</p>',
            esc($fullName),
            esc($email),
            esc($tempPassword),
            esc($loginUrl)
        );

        send_email([
            'to' => $email,
            'to_name' => $fullName,
            'subject' => 'Your Zee Tech Foundation Account Credentials',
            'body' => $emailBody,
            'alt_body' => "Hi $fullName,\n\nAn account has been automatically created for you at Zee Tech Foundation.\n\nLogin Credentials:\nEmail: $email\nTemporary Password: $tempPassword\n\nLogin at: $loginUrl\n\nBest regards,\nZee Tech Foundation",
        ]);

        login_user($newUser);

        return ['user' => $newUser, 'created' => true, 'temp_password' => $tempPassword];
    } catch (Throwable $e) {
        error_log('provision_user_account_if_needed error: ' . $e->getMessage());
        return ['user' => null, 'created' => false, 'temp_password' => null];
    }
}

function update_donation_status(int $id, string $status): bool
{
    if (!in_array($status, ['approved', 'rejected', 'pending'], true)) {
        return false;
    }

    try {
        $db = db_connect();

        try {
            $db->exec("ALTER TABLE donations ADD COLUMN status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending'");
        } catch (Throwable $ignored) {
        }

        $stmt = $db->prepare('UPDATE donations SET status = :status WHERE id = :id LIMIT 1');
        $updated = $stmt->execute([':status' => $status, ':id' => $id]);

        if ($updated) {
            $donation = get_donation_by_id($id);
            if ($donation) {
                $email = $donation['email'];
                $fullName = $donation['full_name'];
                $deviceType = $donation['device_type'];

                if ($status === 'approved') {
                    $subject = 'Your Device Donation Has Been Approved - Zee Tech Foundation';
                    $body = sprintf(
                        '<p>Hi %s,</p>' .
                        '<p>Great news! Your device donation request for <strong>%s</strong> has been <strong>APPROVED</strong> by Zee Tech Foundation.</p>' .
                        '<p>Our team will reach out to you shortly via phone or email to coordinate how to collect the device based on your preference (<em>%s</em>).</p>' .
                        '<p>Thank you for contributing to digital inclusion!</p>' .
                        '<p>Best regards,<br>Zee Tech Foundation Team</p>',
                        esc($fullName),
                        esc($deviceType),
                        esc($donation['handover_preference'] ?? 'Handover')
                    );
                    $altBody = "Hi $fullName,\n\nGreat news! Your device donation request for $deviceType has been APPROVED by Zee Tech Foundation.\n\nOur team will reach out to you shortly to coordinate how to collect the device.\n\nThank you for contributing!\n\nBest regards,\nZee Tech Foundation Team";

                    send_email([
                        'to' => $email,
                        'to_name' => $fullName,
                        'subject' => $subject,
                        'body' => $body,
                        'alt_body' => $altBody,
                    ]);
                } elseif ($status === 'rejected') {
                    $subject = 'Update Regarding Your Device Donation - Zee Tech Foundation';
                    $body = sprintf(
                        '<p>Hi %s,</p>' .
                        '<p>Thank you for offering to donate your <strong>%s</strong> to Zee Tech Foundation.</p>' .
                        '<p>After reviewing your submission, we regret to inform you that we cannot collect this device from you at this time.</p>' .
                        '<p>We truly appreciate your willingness to support our cause.</p>' .
                        '<p>Best regards,<br>Zee Tech Foundation Team</p>',
                        esc($fullName),
                        esc($deviceType)
                    );
                    $altBody = "Hi $fullName,\n\nThank you for offering to donate your $deviceType to Zee Tech Foundation.\n\nAfter reviewing your submission, we regret to inform you that we cannot collect this device from you at this time.\n\nWe truly appreciate your willingness to support our cause.\n\nBest regards,\nZee Tech Foundation Team";

                    send_email([
                        'to' => $email,
                        'to_name' => $fullName,
                        'subject' => $subject,
                        'body' => $body,
                        'alt_body' => $altBody,
                    ]);
                }
            }
        }

        return $updated;
    } catch (Throwable $e) {
        error_log('update_donation_status error: ' . $e->getMessage());
        return false;
    }
}

function update_application_status(int $id, string $status): bool
{
    if (!in_array($status, ['approved', 'rejected', 'pending'], true)) {
        return false;
    }

    try {
        $db = db_connect();
        $stmt = $db->prepare('UPDATE applications SET status = :status WHERE id = :id LIMIT 1');
        $updated = $stmt->execute([':status' => $status, ':id' => $id]);

        if ($updated) {
            $application = get_application_by_id($id);
            if ($application) {
                $email = $application['email'];
                $fullName = $application['full_name'];
                $applicantType = $application['applicant_type'];

                if ($status === 'approved') {
                    $subject = 'Your Device Application Has Been Approved - Zee Tech Foundation';
                    $body = sprintf(
                        '<p>Hi %s,</p>' .
                        '<p>Congratulations! Your device application to Zee Tech Foundation has been <strong>APPROVED</strong>.</p>' .
                        '<p>Application Details:</p>' .
                        '<ul>' .
                        '<li><strong>Reference ID:</strong> APP-%s-%d</li>' .
                        '<li><strong>Applicant Type:</strong> %s</li>' .
                        '</ul>' .
                        '<p>Our team is preparing a refurbished device for dispatch. We will contact you shortly regarding delivery details.</p>' .
                        '<p>Best regards,<br>Zee Tech Foundation Team</p>',
                        esc($fullName),
                        date('Y'),
                        $id,
                        esc($applicantType)
                    );
                    $altBody = "Hi $fullName,\n\nCongratulations! Your device application (APP-" . date('Y') . "-$id) has been APPROVED by Zee Tech Foundation.\n\nOur team is preparing a refurbished device for dispatch. We will contact you shortly regarding delivery details.\n\nBest regards,\nZee Tech Foundation Team";

                    send_email([
                        'to' => $email,
                        'to_name' => $fullName,
                        'subject' => $subject,
                        'body' => $body,
                        'alt_body' => $altBody,
                    ]);
                } elseif ($status === 'rejected') {
                    $subject = 'Update Regarding Your Device Application - Zee Tech Foundation';
                    $body = sprintf(
                        '<p>Hi %s,</p>' .
                        '<p>Thank you for applying for a refurbished device with Zee Tech Foundation.</p>' .
                        '<p>After carefully reviewing your application (Reference ID: APP-%s-%d), we regret to inform you that we are unable to approve your request at this time due to current inventory availability and selection priorities.</p>' .
                        '<p>We encourage you to re-apply in future campaign cycles as more devices become available.</p>' .
                        '<p>Best regards,<br>Zee Tech Foundation Team</p>',
                        esc($fullName),
                        date('Y'),
                        $id
                    );
                    $altBody = "Hi $fullName,\n\nThank you for applying for a refurbished device with Zee Tech Foundation.\n\nAfter carefully reviewing your application (APP-" . date('Y') . "-$id), we regret to inform you that we are unable to approve your request at this time.\n\nWe encourage you to re-apply in future campaign cycles.\n\nBest regards,\nZee Tech Foundation Team";

                    send_email([
                        'to' => $email,
                        'to_name' => $fullName,
                        'subject' => $subject,
                        'body' => $body,
                        'alt_body' => $altBody,
                    ]);
                }
            }
        }

        return $updated;
    } catch (Throwable $e) {
        error_log('update_application_status error: ' . $e->getMessage());
        return false;
    }
}

start_secure_session();
send_security_headers();


