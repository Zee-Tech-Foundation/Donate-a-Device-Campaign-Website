<?php
declare(strict_types=1);

require_once __DIR__ . '/init.php';

$pageTitle = 'Forgot password - Zee Tech Foundation';
$pageDescription = 'Request a password reset link for your account.';

$errors = [];
$success = false;

if (is_post()) {
    $token = $_POST['csrf_token'] ?? null;
    if (!validate_csrf_token($token)) {
        $errors[] = 'Invalid form submission. Please refresh and try again.';
    }

    $email = sanitize_email($_POST['email'] ?? '');
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    if (empty($errors)) {
        $user = get_user_by_email($email);
        if ($user) {
            $resetToken = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));
            if (set_password_reset_token((int) $user['id'], $resetToken, $expiresAt)) {
                $resetLink = SITE_URL . '/reset-password?token=' . urlencode($resetToken);
                $body = sprintf(
                    '<p>Hi %s,</p><p>You requested a password reset for your Zee Tech Foundation account.</p><p><a href="%s">Reset your password</a></p><p>If you did not request this, you can ignore this email.</p><p>Best regards,<br>Zee Tech Foundation</p>',
                    esc((string) $user['name']),
                    esc($resetLink)
                );

                send_email([
                    'to' => $email,
                    'to_name' => (string) $user['name'],
                    'subject' => 'Reset your password',
                    'body' => $body,
                    'alt_body' => "Hi {$user['name']},\n\nYou requested a password reset for your Zee Tech Foundation account.\n\nReset your password here: $resetLink\n\nIf you did not request this, you can ignore this email.\n\nBest regards,\nZee Tech Foundation",
                ]);
            }
        }

        $success = true;
    }
}

require_once __DIR__ . '/templates/header.php';
?>
    <section class="auth-shell">
      <div class="auth-card">
        <a class="brand" href="index" style="justify-content: center; margin-bottom: 1.5rem">
          <img class="brand-logo" src="images/logo.png" width="150px" alt="Zee Tech Foundation logo" />
        </a>
        <h1>Forgot password?</h1>
        <p class="muted">Enter your email and we will send you a reset link.</p>

        <?php if (!empty($errors)): ?>
          <div class="card" style="border-color: #c0392b; background: #fff2f2; color: #6b1d1d; margin-bottom: 1rem;">
            <h3>Reset request failed</h3>
            <ul>
              <?php foreach ($errors as $error): ?>
                <li><?= esc($error); ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <?php if ($success): ?>
          <div class="card" style="border-color: #1a7a4b; background: #f0fbf5; color: #0f3f1e; margin-bottom: 1rem;">
            <h3>Check your inbox</h3>
            <p>If an account exists for that email, we have sent a password reset link.</p>
          </div>
        <?php else: ?>
          <form class="form mt-3" method="post" action="forgot-password">
            <?= csrf_input(); ?>
            <div class="field">
              <label for="email">Email</label>
              <input id="email" name="email" type="email" value="<?= old('email'); ?>" required />
            </div>
            <button class="btn btn-primary" type="submit">Send reset link</button>
          </form>
        <?php endif; ?>

        <p class="text-center mt-3 muted">
          <a href="login">Back to sign in</a>
        </p>
      </div>
    </section>
<?php
require_once __DIR__ . '/templates/footer.php';
