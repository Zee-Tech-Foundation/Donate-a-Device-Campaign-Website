<?php
declare(strict_types=1);

require_once __DIR__ . '/init.php';

$pageTitle = 'Register - Zee Tech Foundation';
$pageDescription = 'Create a Zee Tech Foundation account to manage your donations and applications.';

$errors = [];
$success = false;

if (is_post()) {
    $token = $_POST['csrf_token'] ?? null;
    if (!validate_csrf_token($token)) {
        $errors[] = 'Invalid form submission. Please refresh and try again.';
    }

    $fullName = sanitize_text($_POST['full_name'] ?? '');
    $email = sanitize_email($_POST['email'] ?? '');
    $role = sanitize_text($_POST['role'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($fullName === '') {
        $errors[] = 'Your full name is required.';
    }
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if (!in_array($role, ['donor', 'applicant', 'partner'], true)) {
        $errors[] = 'Please select the correct account type.';
    }
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long.';
    }

    if (empty($errors)) {
        try {
            $db = db_connect();
            $stmt = $db->prepare('SELECT COUNT(*) FROM users WHERE email = :email');
            $stmt->execute([':email' => $email]);
            if ((int) $stmt->fetchColumn() > 0) {
                $errors[] = 'An account already exists with that email address.';
            } else {
                $passwordHash = hash_password($password);
                $stmt = $db->prepare('INSERT INTO users (name, email, password_hash, role, created_at, updated_at) VALUES (:name, :email, :password_hash, :role, NOW(), NOW())');
                $stmt->execute([
                    ':name' => $fullName,
                    ':email' => $email,
                    ':password_hash' => $passwordHash,
                    ':role' => $role,
                ]);
                $success = true;
            }
        } catch (Throwable $e) {
            error_log('Registration failed: ' . $e->getMessage());
            $errors[] = 'Unable to create your account right now. Please try again later.';
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
        <h1>Create your account</h1>
        <p class="muted">Track donations or applications from your dashboard.</p>

        <?php if ($success): ?>
          <div class="card" style="border-color: #1a7a4b; background: #f0fbf5; color: #0f3f1e; margin-bottom: 1rem;">
            <h3>Account created</h3>
            <p>Your account has been created. You can now <a href="login">sign in</a>.</p>
          </div>
        <?php else: ?>
          <?php if (!empty($errors)): ?>
            <div class="card" style="border-color: #c0392b; background: #fff2f2; color: #6b1d1d; margin-bottom: 1rem;">
              <h3>Registration failed</h3>
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
            <span>Sign up with Google</span>
          </a>

          <div class="auth-divider">
            <span>or sign up with email</span>
          </div>

          <?php $currentRole = old('role') !== '' ? old('role') : 'applicant'; ?>

          <form class="form" method="post" action="register">
            <?= csrf_input(); ?>
            <div class="field"><label for="full_name">Full name</label><input id="full_name" name="full_name" type="text" value="<?= old('full_name'); ?>" required /></div>
            <div class="field">
              <label for="email">Email</label>
              <input id="email" name="email" type="email" value="<?= old('email'); ?>" required />
            </div>
            <div class="field">
              <label for="role">I'm signing up as</label>
              <select id="role" name="role" required>
                <option value="applicant" <?= $currentRole === 'applicant' ? 'selected' : ''; ?>>Applicant (Default)</option>
                <option value="donor" <?= $currentRole === 'donor' ? 'selected' : ''; ?>>Donor</option>
                <option value="partner" <?= $currentRole === 'partner' ? 'selected' : ''; ?>>Partner</option>
              </select>
            </div>
            <div class="field">
              <label for="password">Password</label>
              <input id="password" name="password" type="password" required />
            </div>
            <button class="btn btn-primary" type="submit">Create account</button>
          </form>
          <p class="text-center mt-3 muted">
            Already have one? <a href="login">Sign in</a>
          </p>
        <?php endif; ?>
      </div>
    </section>
<?php
require_once __DIR__ . '/templates/footer.php';
