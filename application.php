<?php
declare(strict_types=1);

require_once __DIR__ . '/init.php';
require_admin();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$application = null;

if ($id > 0) {
    $application = get_application_by_id($id);
}

if (is_post()) {
    $token = $_POST['csrf_token'] ?? null;
    if (!validate_csrf_token($token)) {
        http_response_code(400);
        echo 'Invalid form submission.';
        exit;
    }
    if (isset($_POST['delete_id'])) {
        if (delete_application_by_id(sanitize_int($_POST['delete_id'] ?? 0))) {
            redirect('applications');
        }
        http_response_code(500);
        echo 'Unable to delete application.';
        exit;
    } elseif (isset($_POST['update_status']) && $id > 0) {
        $newStatus = sanitize_text($_POST['update_status']);
        if (in_array($newStatus, ['approved', 'rejected'], true)) {
            update_application_status($id, $newStatus);
            redirect('application?id=' . $id . '&status_updated=' . $newStatus);
        }
    }
}

if (!$application) {
    http_response_code(404);
    $pageTitle = 'Application Not Found';
    require_once __DIR__ . '/templates/header.php';
    ?>
    <section class="block">
      <div class="container">
        <h1>Application not found</h1>
        <p class="muted">The requested application could not be located.</p>
        <a class="btn btn-primary" href="applications">Back to applications</a>
      </div>
    </section>
    <?php
    require_once __DIR__ . '/templates/footer.php';
    return;
}

$statusUpdated = $_GET['status_updated'] ?? null;
$currentStatus = $application['status'] ?? 'pending';

$pageTitle = 'Application #' . esc((string)$application['id']);
$pageDescription = 'Application details for ' . esc((string)$application['full_name']) . '.';

require_once __DIR__ . '/templates/header.php';
?>
    <section class="block">
      <div class="container">
        <div class="section-head">
          <p class="eyebrow">Admin</p>
          <h1>Application details</h1>
          <p class="muted">Review the full request and submission details.</p>
        </div>

        <?php if ($statusUpdated === 'approved'): ?>
          <div class="card" style="border-color: #1a7a4b; background: #f0fbf5; color: #0f3f1e; margin-bottom: 1.5rem;">
            <p style="margin:0"><strong>Application Approved:</strong> Status updated to Approved and confirmation email sent to applicant (<?= esc($application['email']); ?>).</p>
          </div>
        <?php elseif ($statusUpdated === 'rejected'): ?>
          <div class="card" style="border-color: #c0392b; background: #fff2f2; color: #6b1d1d; margin-bottom: 1.5rem;">
            <p style="margin:0"><strong>Application Disapproved:</strong> Status updated to Disapproved and notification email sent to applicant (<?= esc($application['email']); ?>).</p>
          </div>
        <?php endif; ?>

        <div class="card">
          <dl class="detail-list">
            <dt>Applicant</dt>
            <dd><?= esc((string)$application['full_name']); ?></dd>

            <dt>Email</dt>
            <dd><?= esc((string)$application['email']); ?></dd>

            <dt>Type</dt>
            <dd><?= esc((string)$application['applicant_type']); ?></dd>

            <dt>Status</dt>
            <dd>
              <span class="badge <?= $currentStatus === 'approved' ? 'success' : ($currentStatus === 'rejected' ? 'danger' : 'gray'); ?>">
                <?= ucfirst(esc($currentStatus)); ?>
              </span>
            </dd>

            <dt>Phone</dt>
            <dd><?= esc((string)$application['phone']); ?></dd>

            <dt>Location</dt>
            <dd><?= nl2br(esc((string)$application['location'])); ?></dd>

            <dt>Reason</dt>
            <dd><?= nl2br(esc((string)$application['reason'])); ?></dd>

            <dt>Submitted</dt>
            <dd><?= esc((string)date('d M, Y - h:i A', strtotime($application['created_at']))); ?></dd>
          </dl>

          <div class="card-footer" style="text-align:right; display:flex; gap:.75rem; justify-content:flex-end; flex-wrap:wrap; align-items:center;">
            <form method="post" action="application?id=<?= esc((string)$application['id']); ?>" style="margin:0;">
              <?= csrf_input(); ?>
              <input type="hidden" name="update_status" value="approved" />
              <button class="btn btn-primary" type="submit" style="background-color: #1a7a4b; border-color: #1a7a4b;" <?= $currentStatus === 'approved' ? 'disabled' : ''; ?>>
                <?= $currentStatus === 'approved' ? 'Approved' : 'Approve Application'; ?>
              </button>
            </form>

            <form method="post" action="application?id=<?= esc((string)$application['id']); ?>" style="margin:0;">
              <?= csrf_input(); ?>
              <input type="hidden" name="update_status" value="rejected" />
              <button class="btn btn-secondary" type="submit" style="background-color: #c0392b; color: #fff; border-color: #c0392b;" <?= $currentStatus === 'rejected' ? 'disabled' : ''; ?> onclick="return confirm('Disapprove this application? A notification email will be sent to the applicant.');">
                <?= $currentStatus === 'rejected' ? 'Disapproved' : 'Disapprove Application'; ?>
              </button>
            </form>

            <form method="post" action="application?id=<?= esc((string)$application['id']); ?>" style="margin:0;">
              <?= csrf_input(); ?>
              <input type="hidden" name="delete_id" value="<?= esc((string)$application['id']); ?>" />
              <button class="btn btn-secondary" type="submit" onclick="return confirm('Delete this application?');">Delete</button>
            </form>
            <a class="btn btn-secondary" href="applications">Back to applications</a>
          </div>
        </div>
      </div>
    </section>

<?php
require_once __DIR__ . '/templates/footer.php';
