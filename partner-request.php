<?php
declare(strict_types=1);

require_once __DIR__ . '/init.php';
require_admin();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$partner = null;

if ($id > 0) {
    $partner = get_partner_by_id($id);
}

if (is_post() && isset($_POST['delete_id'])) {
    $token = $_POST['csrf_token'] ?? null;
    if (!validate_csrf_token($token)) {
        http_response_code(400);
        echo 'Invalid form submission.';
        exit;
    }
    if (delete_partner_by_id(sanitize_int($_POST['delete_id'] ?? 0))) {
        redirect('partner-requests');
    }
    http_response_code(500);
    echo 'Unable to delete partner request.';
    exit;
}

if (!$partner) {
    http_response_code(404);
    $pageTitle = 'Partner request not found';
    require_once __DIR__ . '/templates/header.php';
    ?>
    <section class="block">
      <div class="container">
        <h1>Partner request not found</h1>
        <p class="muted">The requested partner enquiry could not be located.</p>
        <a class="btn btn-primary" href="partner-requests">Back to partner requests</a>
      </div>
    </section>
    <?php
    require_once __DIR__ . '/templates/footer.php';
    return;
}

$pageTitle = 'Partner request #' . esc((string)$partner['id']);
$pageDescription = 'Partner request details for ' . esc((string)$partner['organisation_name']) . '.';

require_once __DIR__ . '/templates/header.php';
?>
    <section class="block">
      <div class="container">
        <div class="section-head">
          <p class="eyebrow">Admin</p>
          <h1>Partner request details</h1>
          <p class="muted">Review the full partnership enquiry submitted by the organisation.</p>
        </div>

        <div class="card">
          <dl class="detail-list">
            <dt>Organisation</dt>
            <dd><?= esc((string)$partner['organisation_name']); ?></dd>

            <dt>Website</dt>
            <dd><?= esc((string)$partner['website']); ?></dd>

            <dt>Contact</dt>
            <dd><?= esc((string)$partner['contact_name']); ?></dd>

            <dt>Email</dt>
            <dd><?= esc((string)$partner['email']); ?></dd>

            <dt>Phone</dt>
            <dd><?= esc((string)$partner['phone']); ?></dd>

            <dt>Type</dt>
            <dd><?= esc((string)$partner['partnership_type']); ?></dd>

            <dt>Message</dt>
            <dd><?= nl2br(esc((string)$partner['message'])); ?></dd>

            <dt>Submitted</dt>
            <dd><?= esc((string)date('d M, Y', strtotime($partner['created_at']))); ?></dd>
          </dl>

          <div class="card-footer" style="text-align:right; display:flex; gap:.75rem; justify-content:flex-end;">
            <form method="post" action="partner-request?id=<?= esc((string)$partner['id']); ?>" style="margin:0;">
              <?= csrf_input(); ?>
              <input type="hidden" name="delete_id" value="<?= esc((string)$partner['id']); ?>" />
              <button class="btn btn-secondary" type="submit" onclick="return confirm('Delete this partner request?');">Delete</button>
            </form>
            <a class="btn btn-secondary" href="partner-requests">Back to partner requests</a>
          </div>
        </div>
      </div>
    </section>
<?php
require_once __DIR__ . '/templates/footer.php';
