<?php
declare(strict_types=1);

require_once __DIR__ . '/init.php';

$pageTitle = 'Device Tracking - Zee Tech Foundation';
$pageDescription = 'Track a donated device from refurbishment through to delivery.';
$pageRobots = 'noindex';

$error = null;
$donation = null;
$timeline = [];

if (is_post()) {
    $reference = sanitize_text($_POST['reference'] ?? '');
    if ($reference === '') {
        $error = 'Please enter a device reference.';
    } else {
        $donation = get_donation_by_reference($reference);
        if ($donation === null) {
            $error = 'No device found for that reference.';
        } else {
            $timeline = get_donation_timeline($donation);
        }
    }
}

require_once __DIR__ . '/templates/header.php';
?>
    <section class="page-hero">
      <div class="container">
        <p class="eyebrow">Device tracking</p>
        <h1>Follow a device from donation to delivery.</h1>
        <p>Enter the device ID from your donation receipt.</p>
      </div>
    </section>

    <section class="block">
      <div class="container" style="max-width: 720px">
        <?php if ($error): ?>
          <div class="card" style="border-color: #c0392b; background: #fff2f2; color: #6b1d1d; margin-bottom: 1rem;">
            <p><?= esc($error); ?></p>
          </div>
        <?php endif; ?>

        <form class="form" method="post" action="device-tracking" style="grid-template-columns: 1fr auto; display: grid">
          <?= csrf_input(); ?>
          <input id="reference" name="reference" placeholder="DEV-XXXX" value="<?= esc($_POST['reference'] ?? ''); ?>" style="padding: 0.65rem 0.75rem; border: 1px solid var(--border); border-radius: 8px;" />
          <button class="btn btn-primary" type="submit">Track</button>
        </form>

        <?php if ($donation): ?>
          <div class="card mt-3">
            <p class="muted" style="margin: 0; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.1em;">Device</p>
            <p id="did" style="margin: 0.25rem 0; font-family: monospace; font-size: 1.1rem; font-weight: 600;">DEV-<?= esc((string)$donation['id']); ?></p>
            <ol class="timeline mt-3">
              <?php foreach ($timeline as $step): ?>
                <li>
                  <div class="dot" style="background: <?= $step['complete'] ? 'var(--accent)' : '#dbe3ec'; ?>; color: <?= $step['complete'] ? '#fff' : 'var(--muted)'; ?>">1</div>
                  <div class="body">
                    <strong><?= esc($step['label']); ?></strong>
                    <small><?= esc($step['date']); ?></small>
                  </div>
                </li>
              <?php endforeach; ?>
            </ol>
          </div>
        <?php endif; ?>
      </div>
    </section>
<?php
require_once __DIR__ . '/templates/footer.php';
