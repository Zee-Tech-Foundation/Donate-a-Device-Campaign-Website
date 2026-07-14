<?php
declare(strict_types=1);

require_once __DIR__ . '/init.php';
require_admin();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$application = null;

if ($id > 0) {
    $application = get_application_by_id($id);
}

if (is_post() && isset($_POST['delete_id'])) {
    $token = $_POST['csrf_token'] ?? null;
    if (!validate_csrf_token($token)) {
        http_response_code(400);
        echo 'Invalid form submission.';
        exit;
    }
    if (delete_application_by_id(sanitize_int($_POST['delete_id'] ?? 0))) {
        redirect('applications');
    }
    http_response_code(500);
    echo 'Unable to delete application.';
    exit;
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

        <div class="card">
          <dl class="detail-list">
            <dt>Applicant</dt>
            <dd><?= esc((string)$application['full_name']); ?></dd>

            <dt>Email</dt>
            <dd><?= esc((string)$application['email']); ?></dd>

            <dt>Type</dt>
            <dd><?= esc((string)$application['applicant_type']); ?></dd>

            <dt>Status</dt>
            <dd><?= esc((string)$application['status']); ?></dd>

            <dt>Phone</dt>
            <dd><?= esc((string)$application['phone']); ?></dd>

            <dt>Location</dt>
            <dd><?= nl2br(esc((string)$application['location'])); ?></dd>

            <dt>Reason</dt>
            <dd><?= nl2br(esc((string)$application['reason'])); ?></dd>

            <dt>Submitted</dt>
            <dd><?= esc((string)date('d M, Y - h:i A', strtotime($application['created_at']))); ?></dd>
          </dl>

          <div class="card-footer" style="text-align:right; display:flex; gap:.75rem; justify-content:flex-end;">
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
