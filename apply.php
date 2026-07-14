<?php
declare(strict_types=1);

require_once __DIR__ . '/init.php';
require_once __DIR__ . '/templates/captcha.php';

$pageTitle = 'Apply for a Device - Zee Tech Foundation';
$pageDescription = 'Students, educators and community programmes can apply here for a refurbished device.';

$errors = [];
$success = false;

if (is_post()) {
    $token = $_POST['csrf_token'] ?? null;
    if (!validate_csrf_token($token)) {
        $errors[] = 'Invalid form submission. Please refresh and try again.';
    }

    $fullName = sanitize_text($_POST['full_name'] ?? '');
    $age = sanitize_int($_POST['age'] ?? '');
    $email = sanitize_email($_POST['email'] ?? '');
    $phone = sanitize_text($_POST['phone'] ?? '');
    $applicantType = sanitize_text($_POST['applicant_type'] ?? '');
    $organisation = sanitize_text($_POST['organisation'] ?? '');
    $reason = sanitize_text($_POST['reason'] ?? '');
    $location = sanitize_text($_POST['location'] ?? '');
    $terms = isset($_POST['terms']);
    $captcha = $_POST['captcha'] ?? '';

    if ($fullName === '') {
        $errors[] = 'Your full name is required.';
    }
    if ($age < 18) {
        $errors[] = 'Please enter your age (must be at least 18).';
    }
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if ($phone === '') {
        $errors[] = 'Please provide a phone number.';
    }
    if ($applicantType === '') {
        $errors[] = 'Please select your applicant type.';
    }
    if ($reason === '') {
        $errors[] = 'Please explain why you need a device.';
    }
    if ($location === '') {
        $errors[] = 'Please provide your location.';
    }
    if (!$terms) {
        $errors[] = 'You must agree to the Terms & Conditions.';
    }
    if (!verify_captcha_answer($captcha)) {
        $errors[] = 'Captcha answer is incorrect. Please try again.';
    }

    if (empty($errors) && application_exists($email)) {
        $errors[] = 'You have already submitted an application. Please wait for our review before submitting another request.';
    }

    if (empty($errors)) {
        try {
            $db = db_connect();
            $stmt = $db->prepare('INSERT INTO applications (full_name, age, email, phone, applicant_type, organisation, reason, location, terms_agreed, created_at) VALUES (:full_name, :age, :email, :phone, :applicant_type, :organisation, :reason, :location, :terms_agreed, NOW())');
            $stmt->execute([
                ':full_name' => $fullName,
                ':age' => $age,
                ':email' => $email,
                ':phone' => $phone,
                ':applicant_type' => $applicantType,
                ':organisation' => $organisation,
                ':reason' => $reason,
                ':location' => $location,
                ':terms_agreed' => $terms ? 1 : 0,
            ]);
            $success = true;
        } catch (Throwable $e) {
            error_log('Application save failed: ' . $e->getMessage());
            $errors[] = 'Unable to submit your application right now. Please try again later.';
        }
    }
}

require_once __DIR__ . '/templates/header.php';
?>
    <section class="form-bg form-bg-apply">
      <div class="container" style="max-width: 720px">
        <div class="form-intro reveal">
          <p class="eyebrow">Apply</p>
          <h1>Request a refurbished device.</h1>
          <p>
            We prioritise applicants based on need, community impact and
            available inventory.
          </p>
        </div>
        <div class="card reveal">
          <?php if ($success): ?>
            <div class="card" style="border-color: #1a7a4b; background: #f0fbf5; color: #0f3f1e;">
              <h3>Application received</h3>
              <p>Thank you. We will review your request and respond within 2 weeks.</p>
            </div>
          <?php else: ?>
            <?php if (!empty($errors)): ?>
              <div class="card" style="border-color: #c0392b; background: #fff2f2; color: #6b1d1d;">
                <h3>Please correct the errors below</h3>
                <ul>
                  <?php foreach ($errors as $error): ?>
                    <li><?= esc($error); ?></li>
                  <?php endforeach; ?>
                </ul>
              </div>
            <?php endif; ?>

            <form class="form mt-3" method="post" action="apply">
              <?= csrf_input(); ?>
              <div class="row-2">
                <div class="field">
                  <label for="full_name">Full name</label>
                  <input id="full_name" name="full_name" type="text" value="<?= old('full_name'); ?>" required />
                </div>
                <div class="field">
                  <label for="age">Age</label>
                  <input id="age" name="age" type="number" min="18" value="<?= esc((string)(max(18, (int)($_POST['age'] ?? 18)))); ?>" required />
                </div>
              </div>
              <div class="row-2">
                <div class="field">
                  <label for="email">Email</label>
                  <input id="email" name="email" type="email" value="<?= old('email'); ?>" required />
                </div>
                <div class="field"><label for="phone">Phone</label><input id="phone" name="phone" type="text" value="<?= old('phone'); ?>" required /></div>
              </div>
              <div class="field">
                <label for="applicant_type">I am a…</label>
                <select id="applicant_type" name="applicant_type" required>
                  <option value="">Select</option>
                  <?php foreach (['Student', 'Educator', 'Community programme'] as $type): ?>
                    <option value="<?= esc($type); ?>" <?= old('applicant_type') === $type ? 'selected' : ''; ?>><?= esc($type); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="field">
                <label for="organisation">School or organisation</label>
                <input id="organisation" name="organisation" type="text" value="<?= old('organisation'); ?>" />
              </div>
              <div class="field">
                <label for="reason">Why do you need a device?</label>
                <textarea id="reason" name="reason" rows="5" required><?= old('reason'); ?></textarea>
              </div>
              <div class="field">
                <label for="location">Location</label>
                <input id="location" name="location" type="text" value="<?= old('location'); ?>" placeholder="City, country" required />
              </div>
              <div class="field field-check">
                <input type="checkbox" id="terms" name="terms" <?= isset($_POST['terms']) ? 'checked' : ''; ?> required />
                <label for="terms">
                  I agree to the <a href="terms" target="_blank">Terms &amp; Conditions</a>
                  and confirm the information above is accurate.
                </label>
              </div>
              <?= render_captcha_field(); ?>
              <button class="btn btn-primary" type="submit">Submit application</button>
            </form>
          <?php endif; ?>
        </div>
      </div>
    </section>
<?php
require_once __DIR__ . '/templates/footer.php';
