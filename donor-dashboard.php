<?php
declare(strict_types=1);

require_once __DIR__ . '/init.php';
require_login();
$user = current_user();
if ($user['role'] !== 'donor') {
    redirect(get_dashboard_route($user['role']));
}

$pageTitle = 'Donor Dashboard - Zee Tech Foundation';
$pageDescription = 'View your donation history and device tracking status.';
$pageRobots = 'noindex';

$donations = get_user_donations($user['email']);
$totalDonations = count($donations);
$delivered = array_filter($donations, fn($item) => true);
$inRefurbishment = array_filter($donations, fn($item) => true);

require_once __DIR__ . '/templates/header.php';
?>
    <section style="background: #eef2f7; padding: 2.5rem 0; border-bottom: 1px solid var(--border);">
      <div class="container">
        <p class="eyebrow" style="color: var(--accent)">Donor dashboard</p>
        <h1>Welcome back, <?= esc($user['name']); ?></h1>
        <p class="muted">Here's what your devices have been up to.</p>
      </div>
    </section>

    <section class="block">
      <div class="container">
        <div class="grid cols-3">
          <div class="card stat">
            <div class="num"><?= esc((string)$totalDonations); ?></div>
            <div class="lbl">Total donations</div>
          </div>
          <div class="card stat">
            <div class="num"><?= esc((string)count($delivered)); ?></div>
            <div class="lbl">Devices delivered</div>
          </div>
          <div class="card stat">
            <div class="num"><?= esc((string)count($inRefurbishment)); ?></div>
            <div class="lbl">In refurbishment</div>
          </div>
        </div>
        <div class="card mt-4" style="padding: 0; overflow: hidden">
          <div style="display: flex; justify-content: space-between; align-items: center; padding: 1.25rem 1.5rem;">
            <h3 style="margin: 0">Your donations</h3>
            <a class="btn btn-primary" href="donate">Donate another</a>
          </div>
          <table class="table">
            <thead>
              <tr>
                <th>Device ID</th>
                <th>Type</th>
                <th>Donated</th>
                <th>Status</th>
                <th>Placed with</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($donations)): ?>
                <tr><td colspan="6">No donations found. Start by submitting one.</td></tr>
              <?php else: ?>
                <?php foreach ($donations as $donation): ?>
                  <tr>
                    <td><code>DEV-<?= esc((string)$donation['id']); ?></code></td>
                    <td><?= esc((string)$donation['device_type']); ?></td>
                    <td><?= esc((string)$donation['created_at']); ?></td>
                    <td><span class="badge <?= 'gray'; ?>"><?= esc('Delivered'); ?></span></td>
                    <td><?= esc('-'); ?></td>
                    <td><a href="device-tracking">Track</a></td>
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
