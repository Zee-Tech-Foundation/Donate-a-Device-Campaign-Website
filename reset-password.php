<?php
declare(strict_types=1);

require_once __DIR__ . '/init.php';

$pageTitle = 'Reset password - Zee Tech Foundation';
$pageDescription = 'Create a new password for your account.';

$errors = [];
$success = false;

$token = sanitize_text($_GET['token'] ?? '');
$user = $token !== '' ? get_user_by_reset_token($token) : null;

if (!$user) {
    $errors[] = 'This password reset link is invalid or has expired.';
}

if (is_post()) {
    $token = sanitize_text($_POST['token'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (!validate_csrf_token($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Invalid form submission. Please refresh and try again.';
    }

    if ($password === '' || strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long.';
    }
    if ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match.';
    }

    $user = get_user_by_reset_token($token);
    if (!$user) {
        $errors[] = 'This password reset link is invalid or has expired.';
    }

    if (empty($errors)) {
        if (update_user_password((int) $user['id'], $password) && clear_password_reset_token((int) $user['id'])) {
            $success = true;
        } else {
            $errors[] = 'Unable to update your password right now. Please try again later.';
        }
    }
}

require_once __DIR__ . '/templates/header.php';
?>
    <section class="auth-shell">
      <div class="auth-card">
        <a class="brand" href="index" style="justify-content: center; margin-bottom: 1.5rem">
          <img class="brand-logo" src="images/logo.png" width="150px" alt="Zee Tech Foundation logo" />
        </a>
        <h1>Reset your password</h1>
        <p class="muted">Choose a new password for your account.</p>

        <?php if (!empty($errors)): ?>
          <div class="card" style="border-color: #c0392b; background: #fff2f2; color: #6b1d1d; margin-bottom: 1rem;">
            <h3>Reset failed</h3>
            <ul>
              <?php foreach ($errors as $error): ?>
                <li><?= esc($error); ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <?php if ($success): ?>
          <div class="card" style="border-color: #1a7a4b; background: #f0fbf5; color: #0f3f1e; margin-bottom: 1rem;">
            <h3>Password updated</h3>
            <p>Your password has been changed successfully. You can now sign in with your new password.</p>
          </div>
        <?php elseif ($user): ?>
          <form class="form mt-3" method="post" action="reset-password">
            <?= csrf_input(); ?>
            <input type="hidden" name="token" value="<?= esc($token); ?>" />
            <div class="field">
              <label for="password">New password</label>
              <input id="password" name="password" type="password" required />
            </div>
            <div class="field">
              <label for="confirm_password">Confirm password</label>
              <input id="confirm_password" name="confirm_password" type="password" required />
            </div>
            <button class="btn btn-primary" type="submit">Save new password</button>
          </form>
        <?php endif; ?>

        <p class="text-center mt-3 muted">
          <a href="login">Back to sign in</a>
        </p>
      </div>
    </section>
<?php
require_once __DIR__ . '/templates/footer.php';
