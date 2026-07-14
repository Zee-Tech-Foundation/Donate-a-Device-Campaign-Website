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
        $errors[] = 'Invalid partner request selected.';
    }

    if (empty($errors) && !delete_partner_by_id($id)) {
        $errors[] = 'Unable to delete the selected partner request.';
    }

    if (empty($errors)) {
        redirect('partner-requests');
    }
}

$partners = get_all_partners();

$pageTitle = 'Partner Requests - Admin';
$pageDescription = 'Review incoming partner requests submitted via the website.';

require_once __DIR__ . '/templates/header.php';
?>
    <section class="block">
      <div class="container">
        <div class="section-head">
          <p class="eyebrow">Admin</p>
          <h1>Partner requests</h1>
          <p class="muted">Review new partnership enquiries and follow up with organisations.</p>
        </div>

        <div class="card" style="overflow-x:auto;">
          <table class="table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Organisation</th>
                <th>Contact</th>
                <th>Type</th>
                <th>Submitted</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($partners)): ?>
                <tr><td colspan="6">No partner requests have been submitted yet.</td></tr>
              <?php else: ?>
                <?php foreach ($partners as $partner): ?>
                  <tr>
                    <td><code>PR-<?= esc((string)$partner['id']); ?></code></td>
                    <td><?= esc((string)$partner['organisation_name']); ?></td>
                    <td><?= esc((string)$partner['contact_name']); ?><br><?= esc((string)$partner['email']); ?></td>
                    <td><?= esc((string)$partner['partnership_type']); ?></td>
                    <td><?= esc((string)date('d M, Y', strtotime($partner['created_at']))); ?></td>
                    <td>
                      <a class="btn btn-ghost" href="partner-request?id=<?= esc((string)$partner['id']); ?>">Details</a>
                      <form method="post" action="partner-requests" style="display:inline-block; margin-left:.5rem;">
                        <?= csrf_input(); ?>
                        <input type="hidden" name="delete_id" value="<?= esc((string)$partner['id']); ?>" />
                        <button class="btn btn-secondary" type="submit" onclick="return confirm('Delete this partner request?');">Delete</button>
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
