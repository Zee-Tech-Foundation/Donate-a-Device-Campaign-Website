<?php
declare(strict_types=1);

require_once __DIR__ . '/init.php';
require_once __DIR__ . '/templates/captcha.php';

$pageTitle = 'Partner With Us - Zee Tech Foundation';
$pageDescription = 'Corporate and community partnerships that help us scale device donation and refurbishment.';

$errors = [];
$success = false;

if (is_post()) {
    $token = $_POST['csrf_token'] ?? null;
    if (!validate_csrf_token($token)) {
        $errors[] = 'Invalid form submission. Please refresh and try again.';
    }

    $organisationName = sanitize_text($_POST['organisation_name'] ?? '');
    $website = sanitize_text($_POST['website'] ?? '');
    $contactName = sanitize_text($_POST['contact_name'] ?? '');
    $contactEmail = sanitize_email($_POST['contact_email'] ?? '');
    $phone = sanitize_text($_POST['phone'] ?? '');
    $partnershipType = sanitize_text($_POST['partnership_type'] ?? '');
    $message = sanitize_text($_POST['message'] ?? '');
    $terms = isset($_POST['terms']);
    $captcha = $_POST['captcha'] ?? '';

    if ($organisationName === '') {
        $errors[] = 'Organisation name is required.';
    }
    if ($website !== '' && !filter_var($website, FILTER_VALIDATE_URL)) {
        $errors[] = 'Please enter a valid website URL or leave it blank.';
    }
    if ($contactName === '') {
        $errors[] = 'Contact name is required.';
    }
    if ($contactEmail === '' || !filter_var($contactEmail, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid contact email address.';
    }
    if ($phone !== '' && !filter_var($phone, FILTER_VALIDATE_REGEXP, ['options' => ['regexp' => '/^\+?[0-9\s\-\(\)]+$/']])) {
        $errors[] = 'Please enter a valid phone number or leave it blank.';
    }
    if ($partnershipType === '') {
        $errors[] = 'Please select a partnership type.';
    }
    if ($website !== '' && !filter_var($website, FILTER_VALIDATE_URL)) {
        $errors[] = 'Please enter a valid website URL or leave it blank.';
    }
    if (!$terms) {
        $errors[] = 'You must agree to the Terms & Conditions.';
    }
    if (!verify_captcha_answer($captcha)) {
        $errors[] = 'Captcha answer is incorrect. Please try again.';
    }

    if (empty($errors) && partner_request_exists($contactEmail, $organisationName)) {
        $errors[] = 'A partnership enquiry from this email or organisation already exists. Please wait while we review your request.';
    }

    if (empty($errors)) {
        try {
            $db = db_connect();
            $stmt = $db->prepare('INSERT INTO partners (organisation_name, website, contact_name, email, phone, partnership_type, message, terms_agreed, created_at) VALUES (:organisation_name, :website, :contact_name, :email, :phone, :partnership_type, :message, :terms_agreed, NOW())');
            $stmt->execute([
                ':organisation_name' => $organisationName,
                ':website' => $website,
                ':contact_name' => $contactName,
                ':email' => $contactEmail,
                ':phone' => sanitize_text($_POST['phone'] ?? ''),
                ':partnership_type' => $partnershipType,
                ':message' => $message,
                ':terms_agreed' => $terms ? 1 : 0,
            ]);
            $success = true;
        } catch (Throwable $e) {
            error_log('Partner save failed: ' . $e->getMessage());
            $errors[] = 'Unable to submit your partnership enquiry right now. Please try again later.';
        }
    }
}

require_once __DIR__ . '/templates/header.php';
?>
    <section class="page-hero">
      <div class="container">
        <p class="eyebrow">Partner</p>
        <h1>Scale digital inclusion with us.</h1>
        <p>
          We work with companies, schools and NGOs to source devices, sponsor
          refurbishment, and reach more learners.
        </p>
      </div>
    </section>

    <section class="block">
      <div class="container">
        <div class="grid cols-3">
          <div class="card reveal">
            <h3>Corporate device drives</h3>
            <p>
              Retire and donate old fleets responsibly with certified data
              wiping.
            </p>
          </div>
          <div class="card reveal delay-1">
            <h3>Refurbishment sponsorship</h3>
            <p>Fund a batch of devices from acquisition to delivery.</p>
          </div>
          <div class="card reveal delay-2">
            <h3>Community deployment</h3>
            <p>
              Partner as a school, NGO or hub to receive and place devices.
            </p>
          </div>
        </div>
      </div>
    </section>

    <section class="form-bg form-bg-partner">
      <div class="container" style="max-width: 720px">
        <div class="form-intro reveal">
          <h2 style="color: #fff">Start a conversation</h2>
          <p>
            Tell us about your organisation and how you'd like to partner.
          </p>
        </div>
        <div class="card reveal">
          <?php if ($success): ?>
            <div class="card" style="border-color: #1a7a4b; background: #f0fbf5; color: #0f3f1e;">
              <h3>Enquiry sent</h3>
              <p>Thank you. A partnership lead will reach out within 3 business days.</p>
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

            <form class="form" method="post" action="partner">
              <?= csrf_input(); ?>
              <div class="row-2">
                <div class="field">
                  <label for="organisation_name">Organisation name</label>
                  <input id="organisation_name" name="organisation_name" type="text" value="<?= old('organisation_name'); ?>" required />
                </div>
                <div class="field">
                  <label for="website">Website</label>
                  <input id="website" name="website" type="url" value="<?= old('website'); ?>" placeholder="https://" />
                </div>
              </div>
              <div class="row-2">
                <div class="field">
                  <label for="contact_name">Contact name</label>
                  <input id="contact_name" name="contact_name" type="text" value="<?= old('contact_name'); ?>" required />
                </div>
                <div class="field">
                  <label for="contact_email">Contact email</label>
                  <input id="contact_email" name="contact_email" type="email" value="<?= old('contact_email'); ?>" required />
                </div>
            </div>
            <div class="field">
              <label for="phone">Phone</label>
              <input id="phone" name="phone" type="tel" value="<?= old('phone'); ?>" placeholder="+234 08012345678" />
            </div>
              <div class="field">
                <label for="partnership_type">Partnership type</label>
                <select id="partnership_type" name="partnership_type" required>
                  <option value="">Select</option>
                  <?php foreach (['Corporate device drive', 'Refurbishment sponsorship', 'Community deployment', 'Other'] as $option): ?>
                    <option value="<?= esc($option); ?>" <?= old('partnership_type') === $option ? 'selected' : ''; ?>><?= esc($option); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="field">
                <label for="message">Tell us more</label>
                <textarea id="message" name="message" rows="5"><?= old('message'); ?></textarea>
              </div>
              <div class="field field-check">
                <input type="checkbox" id="terms" name="terms" <?= isset($_POST['terms']) ? 'checked' : ''; ?> required />
                <label for="terms">
                  I agree to the <a href="terms" target="_blank">Terms &amp; Conditions</a>
                  and am authorised to represent this organisation.
                </label>
              </div>
              <?= render_captcha_field(); ?>
              <button class="btn btn-primary" type="submit">Send enquiry</button>
            </form>
          <?php endif; ?>
        </div>
      </div>
    </section>
<?php
require_once __DIR__ . '/templates/footer.php';
