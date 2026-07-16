<?php
declare(strict_types=1);

require_once __DIR__ . '/init.php';

$pageTitle = 'Sign in - Zee Tech Foundation';
$pageDescription = 'Sign in to your Zee Tech Foundation account.';

$errors = [];

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

        <form class="form mt-3" method="post" action="login">
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
