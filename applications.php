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
            $errors[] = 'Invalid application selected.';
        }
        if (empty($errors) && !delete_application_by_id($id)) {
            $errors[] = 'Unable to delete the selected application.';
        }
    } elseif (isset($_POST['update_status_id'], $_POST['status_value'])) {
        $id = sanitize_int($_POST['update_status_id'] ?? 0);
        $statusVal = sanitize_text($_POST['status_value'] ?? '');
        if ($id > 0 && in_array($statusVal, ['approved', 'rejected'], true)) {
            update_application_status($id, $statusVal);
        }
    }

    if (empty($errors)) {
        redirect('applications');
    }
}

$pageTitle = 'Applications - Admin';
$pageDescription = 'Review submitted device applications.';

$applications = get_all_applications();

require_once __DIR__ . '/templates/header.php';
?>
    <section class="block">
      <div class="container">
        <div class="section-head">
          <p class="eyebrow">Admin</p>
          <h1>All applications</h1>
          <p class="muted">Review and inspect every device application submitted on the platform.</p>
        </div>

        <div class="card" style="overflow-x:auto;">
          <table class="table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Type</th>
                <th>Status</th>
                <th>Submitted</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($applications)): ?>
                <tr><td colspan="6">No applications have been submitted yet.</td></tr>
              <?php else: ?>
                <?php foreach ($applications as $application): ?>
                  <?php $status = $application['status'] ?? 'pending'; ?>
                  <tr>
                    <td><code>APP-<?= date('Y'); ?>-<?= esc((string)$application['id']); ?></code></td>
                    <td><?= esc((string)$application['full_name']); ?></td>
                    <td><?= esc((string)$application['applicant_type']); ?></td>
                    <td>
                      <span class="badge <?= $status === 'approved' ? 'success' : ($status === 'rejected' ? 'danger' : 'gray'); ?>">
                        <?= ucfirst(esc($status)); ?>
                      </span>
                    </td>
                    <td><?= esc((string)date('d M, Y', strtotime($application['created_at']))); ?></td>
                    <td style="white-space:nowrap;">
                      <a class="btn btn-ghost" href="application?id=<?= esc((string)$application['id']); ?>">Details</a>

                      <?php if ($status !== 'approved'): ?>
                        <form method="post" action="applications" style="display:inline-block; margin-left:.25rem;">
                          <?= csrf_input(); ?>
                          <input type="hidden" name="update_status_id" value="<?= esc((string)$application['id']); ?>" />
                          <input type="hidden" name="status_value" value="approved" />
                          <button class="btn btn-primary" type="submit" style="padding:0.4rem 0.6rem; font-size:0.8rem; background-color:#1a7a4b; border-color:#1a7a4b;">Approve</button>
                        </form>
                      <?php endif; ?>

                      <?php if ($status !== 'rejected'): ?>
                        <form method="post" action="applications" style="display:inline-block; margin-left:.25rem;">
                          <?= csrf_input(); ?>
                          <input type="hidden" name="update_status_id" value="<?= esc((string)$application['id']); ?>" />
                          <input type="hidden" name="status_value" value="rejected" />
                          <button class="btn btn-secondary" type="submit" style="padding:0.4rem 0.6rem; font-size:0.8rem; background-color:#c0392b; color:#fff; border-color:#c0392b;" onclick="return confirm('Disapprove this application? A notification email will be sent to the applicant.');">Disapprove</button>
                        </form>
                      <?php endif; ?>

                      <form method="post" action="applications" style="display:inline-block; margin-left:.25rem;">
                        <?= csrf_input(); ?>
                        <input type="hidden" name="delete_id" value="<?= esc((string)$application['id']); ?>" />
                        <button class="btn btn-secondary" type="submit" style="padding:0.4rem 0.6rem; font-size:0.8rem;" onclick="return confirm('Delete this application?');">Delete</button>
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
