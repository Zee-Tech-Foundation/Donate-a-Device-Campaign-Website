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
        $errors[] = 'Invalid donation selected.';
    }

    if (empty($errors) && !delete_donation_by_id($id)) {
        $errors[] = 'Unable to delete the selected donation.';
    }

    if (empty($errors)) {
        redirect('donations');
    }
}

$donations = get_all_donations();

$pageTitle = 'Donations - Admin';
$pageDescription = 'Review device donations submitted by donors.';

require_once __DIR__ . '/templates/header.php';
?>
    <section class="block">
      <div class="container">
        <div class="section-head">
          <p class="eyebrow">Admin</p>
          <h1>All donations</h1>
          <p class="muted">Review device donations and donor contact details.</p>
        </div>

        <div class="card" style="overflow-x:auto;">
          <table class="table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Donor</th>
                <th>Device</th>
                <th>Hand Over Preferences</th>
                <th>Submitted</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($donations)): ?>
                <tr><td colspan="6">No donations have been submitted yet.</td></tr>
              <?php else: ?>
                <?php foreach ($donations as $donation): ?>
                  <tr>
                    <td><code>DEV-<?= esc((string)$donation['id']); ?></code></td>
                    <td><?= esc((string)$donation['full_name']); ?></td>
                    <td><?= esc((string)$donation['device_type']); ?></td>
                    <td><?= esc((string)$donation['handover_preference']); ?></td>
                    <td><?= esc((string) date('d M, Y', strtotime($donation['created_at']))); ?></td>
                    <td>
                      <a class="btn btn-ghost" href="donation?id=<?= esc((string)$donation['id']); ?>">Details</a>
                      <form method="post" action="donations" style="display:inline-block; margin-left:.5rem;">
                        <?= csrf_input(); ?>
                        <input type="hidden" name="delete_id" value="<?= esc((string)$donation['id']); ?>" />
                        <button class="btn btn-secondary" type="submit" onclick="return confirm('Delete this donation?');">Delete</button>
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
