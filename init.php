<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/vendor/autoload.php';

function start_secure_session(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'domain' => $_SERVER['HTTP_HOST'] ?? '',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Strict',
        ]);
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

start_secure_session();
send_security_headers();
