<?php
declare(strict_types=1);

require_once __DIR__ . '/init.php';
require_admin();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$message = null;

if ($id > 0) {
    $message = get_contact_by_id($id);
}

if (is_post() && isset($_POST['delete_id'])) {
    $token = $_POST['csrf_token'] ?? null;
    if (!validate_csrf_token($token)) {
        http_response_code(400);
        echo 'Invalid form submission.';
        exit;
    }
    if (delete_contact_by_id(sanitize_int($_POST['delete_id'] ?? 0))) {
        redirect('contacts');
    }
    http_response_code(500);
    echo 'Unable to delete this message.';
    exit;
}

if (!$message) {
    http_response_code(404);
    $pageTitle = 'Message Not Found';
    require_once __DIR__ . '/templates/header.php';
    ?>
    <section class="block">
      <div class="container">
        <h1>Message not found</h1>
        <p class="muted">The requested contact message could not be located.</p>
        <a class="btn btn-primary" href="contacts">Back to messages</a>
      </div>
    </section>
    <?php
    require_once __DIR__ . '/templates/footer.php';
    return;
}

$pageTitle = 'Message #' . esc((string)$message['id']);
$pageDescription = 'Contact message from ' . esc((string)$message['name']) . '.';

require_once __DIR__ . '/templates/header.php';
?>
    <section class="block">
      <div class="container">
        <div class="section-head">
          <p class="eyebrow">Admin</p>
          <h1>Message details</h1>
          <p class="muted">Read the full contact message and contact details.</p>
        </div>

        <div class="card">
          <dl class="detail-list">
            <dt>From</dt>
            <dd><?= esc((string)$message['name']); ?></dd>

            <dt>Email</dt>
            <dd><?= esc((string)$message['email']); ?></dd>

            <dt>Subject</dt>
            <dd><?= esc((string)$message['subject']); ?></dd>

            <dt>Message</dt>
            <dd><?= nl2br(esc((string)$message['message'])); ?></dd>

            <dt>Submitted</dt>
            <dd><?= esc((string) date('d M, Y', strtotime($message['created_at']))); ?></dd>
          </dl>

          <div class="card-footer" style="text-align:right; display:flex; gap:.75rem; justify-content:flex-end;">
            <form method="post" action="contact-message?id=<?= esc((string)$message['id']); ?>" style="margin:0;">
              <?= csrf_input(); ?>
              <input type="hidden" name="delete_id" value="<?= esc((string)$message['id']); ?>" />
              <button class="btn btn-secondary" type="submit" onclick="return confirm('Delete this message?');">Delete</button>
            </form>
            <a class="btn btn-secondary" href="contacts">Back to messages</a>
          </div>
        </div>
      </div>
    </section>
<?php
require_once __DIR__ . '/templates/footer.php';
