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

    if (isset($_POST['delete_id'])) {
        $id = sanitize_int($_POST['delete_id'] ?? 0);
        if ($id < 1) {
            $errors[] = 'Invalid donation selected.';
        }
        if (empty($errors) && !delete_donation_by_id($id)) {
            $errors[] = 'Unable to delete the selected donation.';
        }
    } elseif (isset($_POST['update_status_id'], $_POST['status_value'])) {
        $id = sanitize_int($_POST['update_status_id'] ?? 0);
        $statusVal = sanitize_text($_POST['status_value'] ?? '');
        if ($id > 0 && in_array($statusVal, ['approved', 'rejected'], true)) {
            update_donation_status($id, $statusVal);
        }
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
                <th>Status</th>
                <th>Hand Over Preferences</th>
                <th>Submitted</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($donations)): ?>
                <tr><td colspan="7">No donations have been submitted yet.</td></tr>
              <?php else: ?>
                <?php foreach ($donations as $donation): ?>
                  <?php $status = $donation['status'] ?? 'pending'; ?>
                  <tr>
                    <td><code>DEV-<?= esc((string)$donation['id']); ?></code></td>
                    <td><?= esc((string)$donation['full_name']); ?></td>
                    <td><?= esc((string)$donation['device_type']); ?></td>
                    <td>
                      <span class="badge <?= $status === 'approved' ? 'success' : ($status === 'rejected' ? 'danger' : 'gray'); ?>">
                        <?= ucfirst(esc($status)); ?>
                      </span>
                    </td>
                    <td><?= esc((string)$donation['handover_preference']); ?></td>
                    <td><?= esc((string) date('d M, Y', strtotime($donation['created_at']))); ?></td>
                    <td style="white-space:nowrap;">
                      <a class="btn btn-ghost" href="donation?id=<?= esc((string)$donation['id']); ?>">Details</a>
                      
                      <?php if ($status !== 'approved'): ?>
                        <form method="post" action="donations" style="display:inline-block; margin-left:.25rem;">
                          <?= csrf_input(); ?>
                          <input type="hidden" name="update_status_id" value="<?= esc((string)$donation['id']); ?>" />
                          <input type="hidden" name="status_value" value="approved" />
                          <button class="btn btn-primary" type="submit" style="padding:0.4rem 0.6rem; font-size:0.8rem; background-color:#1a7a4b; border-color:#1a7a4b;">Approve</button>
                        </form>
                      <?php endif; ?>

                      <?php if ($status !== 'rejected'): ?>
                        <form method="post" action="donations" style="display:inline-block; margin-left:.25rem;">
                          <?= csrf_input(); ?>
                          <input type="hidden" name="update_status_id" value="<?= esc((string)$donation['id']); ?>" />
                          <input type="hidden" name="status_value" value="rejected" />
                          <button class="btn btn-secondary" type="submit" style="padding:0.4rem 0.6rem; font-size:0.8rem; background-color:#c0392b; color:#fff; border-color:#c0392b;" onclick="return confirm('Disapprove this donation? A notification email will be sent to the donor.');">Disapprove</button>
                        </form>
                      <?php endif; ?>

                      <form method="post" action="donations" style="display:inline-block; margin-left:.25rem;">
                        <?= csrf_input(); ?>
                        <input type="hidden" name="delete_id" value="<?= esc((string)$donation['id']); ?>" />
                        <button class="btn btn-secondary" type="submit" style="padding:0.4rem 0.6rem; font-size:0.8rem;" onclick="return confirm('Delete this donation?');">Delete</button>
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
