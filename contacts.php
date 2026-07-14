<?php
declare(strict_types=1);

require_once __DIR__ . '/init.php';
require_admin();

$errors = [];
if (is_post()) {
    $token = $_POST['csrf_token'] ?? null;
    if (!validate_csrf_token($token)) {
        $errors[] = 'Invalid form submission. Please refresh and try again.';
    }

    $id = sanitize_int($_POST['delete_id'] ?? 0);
    if ($id < 1) {
        $errors[] = 'Invalid contact message selected.';
    }

    if (empty($errors) && !delete_contact_by_id($id)) {
        $errors[] = 'Unable to delete the selected contact message.';
    }

    if (empty($errors)) {
        redirect('contacts');
    }
}

$contacts = get_all_contacts();

$pageTitle = 'Contact Messages - Admin';
$pageDescription = 'Review incoming contact form submissions.';

require_once __DIR__ . '/templates/header.php';
?>
    <section class="block">
      <div class="container">
        <div class="section-head">
          <p class="eyebrow">Admin</p>
          <h1>Contact messages</h1>
          <p class="muted">Review messages sent through the contact form.</p>
        </div>

        <div class="card" style="overflow-x:auto;">
          <table class="table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Subject</th>
                <th>Submitted</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($contacts)): ?>
                <tr><td colspan="6">No messages have been received yet.</td></tr>
              <?php else: ?>
                <?php foreach ($contacts as $message): ?>
                  <tr>
                    <td><code>MSG-<?= esc((string)$message['id']); ?></code></td>
                    <td><?= esc((string)$message['name']); ?></td>
                    <td><?= esc((string)$message['email']); ?></td>
                    <td><?= esc((string)$message['subject']); ?></td>
                    <td><?= esc((string) date('d M, Y', strtotime($message['created_at']))); ?></td>
                    <td>
                      <a class="btn btn-ghost" href="contact-message?id=<?= esc((string)$message['id']); ?>">Details</a>
                      <form method="post" action="contacts" style="display:inline-block; margin-left:.5rem;">
                        <?= csrf_input(); ?>
                        <input type="hidden" name="delete_id" value="<?= esc((string)$message['id']); ?>" />
                        <button class="btn btn-secondary" type="submit" onclick="return confirm('Delete this message?');">Delete</button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </section>
<?php
require_once __DIR__ . '/templates/footer.php';
