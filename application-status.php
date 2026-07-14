<?php
declare(strict_types=1);

require_once __DIR__ . '/init.php';

$pageTitle = 'Application Status - Zee Tech Foundation';
$pageDescription = 'Lookup the status of your application using its reference number.';
$pageRobots = 'noindex';

$status = null;
$error = null;

if (is_post()) {
    $reference = sanitize_text($_POST['reference'] ?? '');
    if ($reference === '') {
        $error = 'Please enter an application reference.';
    } else {
        $application = get_application_by_reference($reference);
        if ($application === null) {
            $error = 'No application found for that reference.';
        } else {
            $status = $application;
        }
    }
}

require_once __DIR__ . '/templates/header.php';
?>
    <section class="page-hero">
      <div class="container">
        <p class="eyebrow">Application status</p>
        <h1>Look up your application.</h1>
        <p>Enter the reference number from your confirmation email.</p>
      </div>
    </section>

    <section class="block">
      <div class="container" style="max-width: 720px">
        <?php if ($error): ?>
          <div class="card" style="border-color: #c0392b; background: #fff2f2; color: #6b1d1d; margin-bottom: 1rem;">
            <p><?= esc($error); ?></p>
          </div>
        <?php endif; ?>
        <form class="form" method="post" action="application-status" style="grid-template-columns: 1fr auto; display: grid">
          <?= csrf_input(); ?>
          <input id="reference" name="reference" placeholder="APP-XXXX" value="<?= esc($_POST['reference'] ?? ''); ?>" style="padding: 0.65rem 0.75rem; border: 1px solid var(--border); border-radius: 8px;" />
          <button class="btn btn-primary" type="submit">Check status</button>
        </form>

        <?php if ($status): ?>
          <div class="card mt-3">
            <div style="display: flex; justify-content: space-between; flex-wrap: wrap; gap: 0.5rem;">
              <div>
                <p class="muted" style="margin: 0; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.1em;">Reference</p>
                <p style="margin: 0.25rem 0; font-family: monospace">APP-<?= date('Y'); ?>-<?= esc((string)$status['id']); ?></p>
              </div>
              <span class="badge"><?= esc((string)$status['status']); ?></span>
            </div>
            <p class="mt-2 muted">Submitted <?= esc((string)$status['created_at']); ?>. We'll update you by email once the status changes.</p>
          </div>
        <?php endif; ?>
      </div>
    </section>
<?php
require_once __DIR__ . '/templates/footer.php';
