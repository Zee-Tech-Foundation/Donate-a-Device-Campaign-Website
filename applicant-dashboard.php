<?php
declare(strict_types=1);

require_once __DIR__ . '/init.php';
require_login();
$user = current_user();
if ($user['role'] !== 'applicant') {
    redirect(get_dashboard_route($user['role']));
}

$pageTitle = 'Applicant Dashboard - Zee Tech Foundation';
$pageDescription = 'Track the status of your device application.';
$pageRobots = 'noindex';

$applications = get_user_applications($user['email']);

require_once __DIR__ . '/templates/header.php';
?>
    <section style="background: #eef2f7; padding: 2.5rem 0; border-bottom: 1px solid var(--border);">
      <div class="container">
        <p class="eyebrow" style="color: var(--accent)">Applicant dashboard</p>
        <h1>Your application</h1>
        <p class="muted">Track the status of your device request.</p>
      </div>
    </section>

    <section class="block">
      <div class="container" style="max-width: 820px">
        <?php if (empty($applications)): ?>
          <div class="card">
            <h3>No applications found</h3>
            <p class="muted">You currently have no submitted applications. You can apply for a device now.</p>
            <a class="btn btn-primary" href="apply">Submit application</a>
          </div>
        <?php else: ?>
          <?php $application = $applications[0]; ?>
          <div class="card">
            <div style="display: flex; justify-content: space-between; flex-wrap: wrap; gap: 0.5rem;">
              <div>
                <p class="muted" style="margin: 0; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.1em;">Application ID</p>
                <p style="margin: 0.25rem 0; font-family: monospace">APP-<?= date('Y'); ?>-<?= esc((string)$application['id']); ?></p>
              </div>
              <span class="badge <?= $application['status'] !== 'pending' ? '' : 'gray'; ?>"><?= esc((string)$application['status']); ?></span>
            </div>
            <ol class="timeline mt-3">
              <li>
                <div class="dot">1</div>
                <div class="body">
                  <strong>Submitted</strong><small><?= esc((string)$application['created_at']); ?></small>
                </div>
              </li>
              <li>
                <div class="dot">2</div>
                <div class="body">
                  <strong>Under review</strong><small>Currently in this stage</small>
                </div>
              </li>
              <li>
                <div class="dot" style="background: #dbe3ec; color: var(--muted)">3</div>
                <div class="body">
                  <strong>Approved</strong><small>Pending</small>
                </div>
              </li>
              <li>
                <div class="dot" style="background: #dbe3ec; color: var(--muted)">4</div>
                <div class="body">
                  <strong>Device dispatched</strong><small>Pending</small>
                </div>
              </li>
            </ol>
            <a class="btn btn-ghost mt-3" href="application-status">Check another application</a>
          </div>
        <?php endif; ?>
      </div>
    </section>
<?php
require_once __DIR__ . '/templates/footer.php';
