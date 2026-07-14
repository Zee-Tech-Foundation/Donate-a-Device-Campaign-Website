<?php
declare(strict_types=1);

require_once __DIR__ . '/init.php';
require_once __DIR__ . '/templates/captcha.php';

$pageTitle = 'Donate a Device - Zee Tech Foundation';
$pageDescription = 'Donate your unused laptop, tablet or phone to a learner in need.';

$errors = [];
$success = false;

if (is_post()) {
    $token = $_POST['csrf_token'] ?? null;
    if (!validate_csrf_token($token)) {
        $errors[] = 'Invalid form submission. Please refresh and try again.';
    }

    $fullName = sanitize_text($_POST['full_name'] ?? '');
    $email = sanitize_email($_POST['email'] ?? '');
    $phone = sanitize_text($_POST['phone'] ?? '');
    $city = sanitize_text($_POST['city'] ?? '');
    $deviceType = sanitize_text($_POST['device_type'] ?? '');
    $quantity = sanitize_int($_POST['quantity'] ?? '1');
    $conditionNotes = sanitize_text($_POST['condition_notes'] ?? '');
    $handoverPreference = sanitize_text($_POST['handover_preference'] ?? '');
    $terms = isset($_POST['terms']);
    $captcha = $_POST['captcha'] ?? '';

    if ($fullName === '') {
        $errors[] = 'Your full name is required.';
    }
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if ($phone === '') {
        $errors[] = 'Please provide a phone number.';
    }
    if ($city === '') {
        $errors[] = 'Please provide your city.';
    }
    if ($deviceType === '') {
        $errors[] = 'Please select a device type.';
    }
    if ($quantity < 1) {
        $errors[] = 'Please select at least one device.';
    }
    if ($handoverPreference === '') {
        $errors[] = 'Please choose a handover preference.';
    }
    if (!$terms) {
        $errors[] = 'You must agree to the Terms & Conditions.';
    }
    if (!verify_captcha_answer($captcha)) {
        $errors[] = 'Captcha answer is incorrect. Please try again.';
    }

    if (empty($errors)) {
        try {
            $db = db_connect();
            $stmt = $db->prepare('INSERT INTO donations (full_name, email, phone, city, device_type, quantity, condition_notes, handover_preference, terms_agreed, created_at) VALUES (:full_name, :email, :phone, :city, :device_type, :quantity, :condition_notes, :handover_preference, :terms_agreed, NOW())');
            $stmt->execute([
                ':full_name' => $fullName,
                ':email' => $email,
                ':phone' => $phone,
                ':city' => $city,
                ':device_type' => $deviceType,
                ':quantity' => $quantity,
                ':condition_notes' => $conditionNotes,
                ':handover_preference' => $handoverPreference,
                ':terms_agreed' => $terms ? 1 : 0,
            ]);

            $success = true;

            $thankYouBody = sprintf(
                '<p>Hi %s,</p><p>Thank you for offering to donate %d %s to Zee Tech Foundation.</p><p>We will contact you shortly to arrange the handover and ensure your device is securely wiped.</p><p>Best regards,<br>Zee Tech Foundation</p>',
                esc($fullName),
                $quantity,
                esc($deviceType)
            );

            send_email([
                'to' => $email,
                'to_name' => $fullName,
                'subject' => 'Thank you for your donation',
                'body' => $thankYouBody,
                'alt_body' => "Hi $fullName,\n\nThank you for offering to donate $quantity $deviceType to Zee Tech Foundation. We will contact you shortly to arrange the handover and ensure your device is securely wiped.\n\nBest regards,\nZee Tech Foundation",
            ]);

            $adminBody = sprintf(
                '<p>New donation received:</p><ul><li>Name: %s</li><li>Email: %s</li><li>Phone: %s</li><li>City: %s</li><li>Device Type: %s</li><li>Quantity: %d</li><li>Handover: %s</li><li>Notes: %s</li></ul><p>Please review and follow up as appropriate.</p>',
                esc($fullName),
                esc($email),
                esc($phone),
                esc($city),
                esc($deviceType),
                $quantity,
                esc($handoverPreference),
                nl2br(esc($conditionNotes))
            );

            send_email([
                'to' => ADMIN_EMAIL,
                'subject' => 'New device donation submitted',
                'body' => $adminBody,
                'alt_body' => "New donation received:\nName: $fullName\nEmail: $email\nPhone: $phone\nCity: $city\nDevice Type: $deviceType\nQuantity: $quantity\nHandover: $handoverPreference\nNotes: $conditionNotes",
            ]);
        } catch (Throwable $e) {
            error_log('Donation save failed: ' . $e->getMessage());
            $errors[] = 'Unable to submit your donation request right now. Please try again later.';
        }
    }
}

require_once __DIR__ . '/templates/header.php';
?>
    <section class="form-bg form-bg-donate">
      <div class="container" style="max-width: 720px">
        <div class="form-intro reveal">
          <p class="eyebrow">Donate</p>
          <h1>Turn your old device into a learner's future.</h1>
          <p>
            Fill in the form and we'll arrange collection or drop-off. All
            data is professionally wiped.
          </p>
        </div>
        <div class="card reveal">
          <?php if ($success): ?>
            <div class="card" style="border-color: #1a7a4b; background: #f0fbf5; color: #0f3f1e;">
              <h3>Donation submitted</h3>
              <p>Thank you. We will contact you soon to arrange handover.</p>
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

            <form class="form mt-3" method="post" action="donate">
              <?= csrf_input(); ?>
              <div class="row-2">
                <div class="field">
                  <label for="full_name">Full name</label>
                  <input id="full_name" name="full_name" type="text" value="<?= old('full_name'); ?>" required />
                </div>
                <div class="field">
                  <label for="email">Email</label>
                  <input id="email" name="email" type="email" value="<?= old('email'); ?>" required />
                </div>
              </div>
              <div class="row-2">
                <div class="field"><label for="phone">Phone</label><input id="phone" name="phone" type="text" value="<?= old('phone'); ?>" required /></div>
                <div class="field"><label for="city">City</label><input id="city" name="city" type="text" value="<?= old('city'); ?>" required /></div>
              </div>
              <div class="row-2">
                <div class="field">
                  <label for="device_type">Device type</label>
                  <select id="device_type" name="device_type" required>
                    <option value="">Select</option>
                    <?php foreach (['Laptop', 'Tablet', 'Smartphone', 'Desktop', 'Other'] as $type): ?>
                      <option value="<?= esc($type); ?>" <?= old('device_type') === $type ? 'selected' : ''; ?>><?= esc($type); ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="field">
                  <label for="quantity">Number of devices</label>
                  <input id="quantity" name="quantity" type="number" min="1" value="<?= esc((string)(max(1, (int)($_POST['quantity'] ?? 1)))); ?>" required />
                </div>
              </div>
              <div class="field">
                <label for="condition_notes">Condition &amp; notes</label>
                <textarea id="condition_notes" name="condition_notes" rows="4" placeholder="Age, condition, any accessories included…"><?= old('condition_notes'); ?></textarea>
              </div>
              <div class="field">
                <label for="handover_preference">Preferred handover</label>
                <select id="handover_preference" name="handover_preference" required>
                  <?php foreach (['Drop-off', 'Pickup', 'Ship'] as $option): ?>
                    <option value="<?= esc($option); ?>" <?= old('handover_preference') === $option ? 'selected' : ''; ?>><?= esc($option); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="field field-check">
                <input type="checkbox" id="terms" name="terms" <?= isset($_POST['terms']) ? 'checked' : ''; ?> required />
                <label for="terms">
                  I agree to the <a href="terms" target="_blank">Terms &amp; Conditions</a>
                  and confirm I own this device and have removed personal data
                  (or authorise Zee Tech to wipe it).
                </label>
              </div>
              <?= render_captcha_field(); ?>
              <button class="btn btn-primary" type="submit">Submit donation</button>
              <p class="hint" style="color: rgba(255, 255, 255, 0.9)">
                Prefer to talk first?
                <a href="contact" style="color: #fff; text-decoration: underline">Contact us</a>.
              </p>
            </form>
          <?php endif; ?>
        </div>
      </div>
    </section>
<?php
require_once __DIR__ . '/templates/footer.php';
