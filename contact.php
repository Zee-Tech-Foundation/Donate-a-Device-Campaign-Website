<?php
declare(strict_types=1);

require_once __DIR__ . '/init.php';
require_once __DIR__ . '/templates/captcha.php';

$pageTitle = 'Contact - Zee Tech Foundation';
$pageDescription = 'Get in touch with Zee Tech Foundation.';

$errors = [];
$success = false;

if (is_post()) {
    $token = $_POST['csrf_token'] ?? null;
    if (!validate_csrf_token($token)) {
        $errors[] = 'Invalid form submission. Please refresh and try again.';
    }

    $name = sanitize_text($_POST['name'] ?? '');
    $email = sanitize_email($_POST['email'] ?? '');
    $subject = sanitize_text($_POST['subject'] ?? '');
    $message = sanitize_text($_POST['message'] ?? '');
    $captcha = $_POST['captcha'] ?? '';

    if ($name === '') {
        $errors[] = 'Your name is required.';
    }
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if ($subject === '') {
        $errors[] = 'The subject is required.';
    }
    if ($message === '') {
        $errors[] = 'The message cannot be empty.';
    }
    if (!verify_captcha_answer($captcha)) {
        $errors[] = 'Captcha answer is incorrect. Please try again.';
    }

    if (empty($errors)) {
        try {
            $db = db_connect();
            $stmt = $db->prepare('INSERT INTO contact_messages (name, email, subject, message, created_at) VALUES (:name, :email, :subject, :message, NOW())');
            $stmt->execute([
                ':name' => $name,
                ':email' => $email,
                ':subject' => $subject,
                ':message' => $message,
            ]);
            $success = true;
        } catch (Throwable $e) {
            error_log('Contact save failed: ' . $e->getMessage());
            $errors[] = 'Unable to send your message right now. Please try again later.';
        }
    }
}

require_once __DIR__ . '/templates/header.php';
?>
    <section class="page-hero">
      <div class="container">
        <p class="eyebrow">Contact</p>
        <h1>We'd love to hear from you.</h1>
        <p>Whether you're donating, applying or partnering - get in touch.</p>
      </div>
    </section>

    <section class="block">
      <div class="container">
        <div class="grid cols-2">
          <div class="card">
            <h3>Direct</h3>
            <p><strong>Email</strong><br /><?= esc(SITE_EMAIL); ?></p>
            <p><strong>Phone</strong><br />+234 810 326 9627</p>
            <p>
              <strong>Address</strong><br />Isa Yuguda House, Plot 19/23, Jos Road<br />Bauchi, Bauchi State, Nigeria
            </p>
            <p>
              <strong>Follow</strong><br />
              <a href="https://x.com/ZeeTechF">Twitter</a> · <a href="https://instagram.com/zeetech_foundation">Instagram</a> · <a href="https://linkedin.com/company/zee-tech-foundation">LinkedIn</a>
            </p>
          </div>

          <div class="card">
            <?php if ($success): ?>
              <div class="card" style="border-color: #1a7a4b; background: #f0fbf5; color: #0f3f1e;">
                <h3>Message sent</h3>
                <p>Thank you for contacting us. We will reply soon.</p>
              </div>
            <?php else: ?>
              <?php if (!empty($errors)): ?>
                <div class="card" style="border-color: #c0392b; background: #fff2f2; color: #6b1d1d;">
                  <h3>There are problems with your form</h3>
                  <ul>
                    <?php foreach ($errors as $error): ?>
                      <li><?= esc($error); ?></li>
                    <?php endforeach; ?>
                  </ul>
                </div>
              <?php endif; ?>

              <form class="form" method="post" action="contact">
                <?= csrf_input(); ?>
                <div class="field">
                  <label for="name">Your name</label>
                  <input id="name" name="name" type="text" value="<?= old('name'); ?>" required />
                </div>
                <div class="field">
                  <label for="email">Email</label>
                  <input id="email" name="email" type="email" value="<?= old('email'); ?>" required />
                </div>
                <div class="field">
                  <label for="subject">Subject</label>
                  <input id="subject" name="subject" type="text" value="<?= old('subject'); ?>" required />
                </div>
                <div class="field">
                  <label for="message">Message</label>
                  <textarea id="message" name="message" rows="5" required><?= old('message'); ?></textarea>
                </div>
                <?= render_captcha_field(); ?>
                <button class="btn btn-primary" type="submit">Send message</button>
              </form>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </section>

    <section class="block alt">
        <div class="container">
          <div class="section-head center">
            <p class="eyebrow">FAQ</p>
            <h2>Frequently Asked Questions</h2>
          </div>

          <div class="grid cols-2">
            <div class="card">
              <h3>Can I donate one laptop?</h3>
              <p>Yes. Every donated device helps create new opportunities.</p>
            </div>

            <div class="card">
              <h3>Do you accept corporate donations?</h3>
              <p>
                Yes. We work with businesses, schools and organisations of all
                sizes.
              </p>
            </div>

            <div class="card">
              <h3>Can I volunteer without technical skills?</h3>
              <p>
                Absolutely. We welcome volunteers with a wide range of skills.
              </p>
            </div>

            <div class="card">
              <h3>Do you operate outside Bauchi?</h3>
              <p>
                Yes. We are expanding our programmes across Nigeria through
                partnerships.
              </p>
            </div>
          </div>
        </div>
      </section>
<?php
require_once __DIR__ . '/templates/footer.php';
