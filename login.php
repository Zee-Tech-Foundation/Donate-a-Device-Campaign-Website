<?php
declare(strict_types=1);

require_once __DIR__ . '/init.php';

$pageTitle = 'Sign in - Zee Tech Foundation';
$pageDescription = 'Sign in to your Zee Tech Foundation account.';

$errors = [];

if (isset($_GET['error'])) {
    $err = $_GET['error'];
    if ($err === 'google_auth_cancelled') {
        $errors[] = 'Google sign-in was cancelled.';
    } elseif ($err === 'google_auth_failed') {
        $errors[] = 'Failed to authenticate with Google. Please try again.';
    } elseif ($err === 'google_not_configured') {
        $errors[] = 'Google Sign-In is not configured yet. Please enter your Client ID in config.php.';
    } elseif ($err === 'account_creation_failed') {
        $errors[] = 'Could not create account via Google. Please try standard registration.';
    } elseif ($err === 'invalid_state') {
        $errors[] = 'Security token mismatch. Please try signing in again.';
    }
}

if (is_post()) {
    $token = $_POST['csrf_token'] ?? null;
    if (!validate_csrf_token($token)) {
        $errors[] = 'Invalid form submission. Please refresh and try again.';
    }

    $email = sanitize_email($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if ($password === '') {
        $errors[] = 'Please enter your password.';
    }

    if (empty($errors)) {
        try {
            $db = db_connect();
            $stmt = $db->prepare('SELECT id, name, email, password_hash, role FROM users WHERE email = :email LIMIT 1');
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch();

            if (!$user || !verify_password($password, (string)$user['password_hash'])) {
                $errors[] = 'Email or password is incorrect.';
            } else {
                login_user($user);
                redirect(get_dashboard_route($user['role']));
            }
        } catch (Throwable $e) {
            error_log('Login failed: ' . $e->getMessage());
            $errors[] = 'Unable to sign in right now. Please try again later.';
        }
    }
}

require_once __DIR__ . '/templates/header.php';
?>
    <section class="auth-shell">
      <div class="auth-card">
        <a class="brand" href="index" style="justify-content: center; margin-bottom: 1.5rem">
          <img class="brand-logo" src="images/logo.png" width="150px" alt="Zee Tech Foundation logo" />
          <!-- <span class="brand-name">Zee Tech <span>Foundation</span></span> -->
        </a>
        <h1>Welcome back</h1>
        <p class="muted">Sign in to your dashboard.</p>

        <?php if (!empty($errors)): ?>
          <div class="card" style="border-color: #c0392b; background: #fff2f2; color: #6b1d1d; margin-bottom: 1rem;">
            <h3>Sign in failed</h3>
            <ul>
              <?php foreach ($errors as $error): ?>
                <li><?= esc($error); ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <a href="google-auth" class="btn-google mt-3">
          <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path fill="#4285F4" d="M23.745 12.27c0-.7-.06-1.4-.19-2.07H12v4.51h6.6c-.29 1.52-1.14 2.82-2.4 3.68v3.05h3.88c2.27-2.09 3.665-5.17 3.665-9.17z"/>
            <path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.88-3.05c-1.08.72-2.45 1.16-4.05 1.16-3.12 0-5.77-2.1-6.72-4.93H1.29v3.14C3.26 21.3 7.31 24 12 24z"/>
            <path fill="#FBBC05" d="M5.28 14.27c-.25-.72-.38-1.49-.38-2.27s.13-1.55.38-2.27V6.59H1.29C.47 8.23 0 10.06 0 12s.47 3.77 1.29 5.41l3.99-3.14z"/>
            <path fill="#EA4335" d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.42-3.42C17.95 1.19 15.24 0 12 0 7.31 0 3.26 2.7 1.29 6.59l3.99 3.14c.95-2.83 3.6-4.98 6.72-4.98z"/>
          </svg>
          <span>Sign in with Google</span>
        </a>

        <div class="auth-divider">
          <span>or sign in with email</span>
        </div>

        <form class="form" method="post" action="login">
          <?= csrf_input(); ?>
          <div class="field">
            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="<?= old('email'); ?>" required />
          </div>
          <div class="field">
            <label for="password">Password</label>
            <input id="password" name="password" type="password" required />
          </div>
          <button class="btn btn-primary" type="submit">Sign in</button>
        </form>
        <p class="text-center mt-3 muted">
          No account? <a href="register">Register</a>
        </p>
        <p class="text-center mt-2 muted">
          Forgot password? <a href="forgot-password">Reset it</a>
        </p>
      </div>
    </section>

<?php
require_once __DIR__ . '/templates/footer.php';
