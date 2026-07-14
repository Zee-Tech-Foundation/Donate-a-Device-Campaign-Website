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
        $errors[] = 'Invalid application selected.';
    }

    if (empty($errors) && !delete_application_by_id($id)) {
        $errors[] = 'Unable to delete the selected application.';
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
                  <tr>
                    <td><code>APP-<?= date('Y'); ?>-<?= esc((string)$application['id']); ?></code></td>
                    <td><?= esc((string)$application['full_name']); ?></td>
                    <td><?= esc((string)$application['applicant_type']); ?></td>
                    <td><span class="badge <?= esc((string)$application['status']) === 'pending' ? 'gray' : ''; ?>"><?= esc((string)$application['status']); ?></span></td>
                    <td><?= esc((string)date('d M, Y', strtotime($application['created_at']))); ?></td>
                    <td>
                      <a class="btn btn-ghost" href="application?id=<?= esc((string)$application['id']); ?>">Details</a>
                      <form method="post" action="applications" style="display:inline-block; margin-left:.5rem;">
                        <?= csrf_input(); ?>
                        <input type="hidden" name="delete_id" value="<?= esc((string)$application['id']); ?>" />
                        <button class="btn btn-secondary" type="submit" onclick="return confirm('Delete this application?');">Delete</button>
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
