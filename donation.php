<?php
declare(strict_types=1);

require_once __DIR__ . '/init.php';
require_admin();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$donation = null;

if ($id > 0) {
    $donation = get_donation_by_id($id);
}

if (is_post() && isset($_POST['delete_id'])) {
    $token = $_POST['csrf_token'] ?? null;
    if (!validate_csrf_token($token)) {
        http_response_code(400);
        echo 'Invalid form submission.';
        exit;
    }
    if (delete_donation_by_id(sanitize_int($_POST['delete_id'] ?? 0))) {
        redirect('donations');
    }
    http_response_code(500);
    echo 'Unable to delete donation.';
    exit;
}

if (!$donation) {
    http_response_code(404);
    $pageTitle = 'Donation Not Found';
    require_once __DIR__ . '/templates/header.php';
    ?>
    <section class="block">
      <div class="container">
        <h1>Donation not found</h1>
        <p class="muted">The requested donation could not be located.</p>
        <a class="btn btn-primary" href="donations">Back to donations</a>
      </div>
    </section>
    <?php
    require_once __DIR__ . '/templates/footer.php';
    return;
}

$pageTitle = 'Donation #' . esc((string)$donation['id']);
$pageDescription = 'Donation details for ' . esc((string)$donation['full_name']) . '.';

require_once __DIR__ . '/templates/header.php';
?>
    <section class="block">
      <div class="container">
        <div class="section-head">
          <p class="eyebrow">Admin</p>
          <h1>Donation details</h1>
          <p class="muted">Review the donor and device information submitted.</p>
        </div>

        <div class="card">
          <dl class="detail-list">
            <dt>Donor</dt>
            <dd><?= esc((string)$donation['full_name']); ?></dd>

            <dt>Email</dt>
            <dd><?= esc((string)$donation['email']); ?></dd>

            <dt>Phone</dt>
            <dd><?= esc((string)$donation['phone']); ?></dd>

            <dt>Device Type</dt>
            <dd><?= esc((string)$donation['device_type']); ?></dd>

            <dt>Condition</dt>
            <dd><?= esc((string)$donation['condition_notes']); ?></dd>

            <dt>Location</dt>
            <dd><?= nl2br(esc((string)$donation['city'])); ?></dd>

            <dt>Hand Over Preferences</dt>
            <dd><?= nl2br(esc((string)$donation['handover_preference'])); ?></dd>

            <dt>Submitted</dt>
            <dd><?= esc((string) date('d M, Y - h:i A', strtotime($donation['created_at']))); ?></dd>
          </dl>

          <div class="card-footer" style="text-align:right; display:flex; gap:.75rem; justify-content:flex-end;">
            <form method="post" action="donation?id=<?= esc((string)$donation['id']); ?>" style="margin:0;">
              <?= csrf_input(); ?>
              <input type="hidden" name="delete_id" value="<?= esc((string)$donation['id']); ?>" />
              <button class="btn btn-secondary" type="submit" onclick="return confirm('Delete this donation?');">Delete</button>
            </form>
            <a class="btn btn-secondary" href="donations">Back to donations</a>
          </div>
        </div>
      </div>
    </section>
<?php
require_once __DIR__ . '/templates/footer.php';
