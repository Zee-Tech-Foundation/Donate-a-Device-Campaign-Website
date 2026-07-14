<?php
declare(strict_types=1);

require_once __DIR__ . '/init.php';
require_admin();

$pageTitle = 'Admin Dashboard - Zee Tech Foundation';
$pageDescription = 'Admin dashboard for Zee Tech Foundation.';

try {
    $db = db_connect();

    $stats = [];
    $summaryQueries = [
        'devices_in_refurbishment' => 'SELECT COUNT(*) FROM donations',
        'open_applications' => 'SELECT COUNT(*) FROM applications WHERE status = "pending"',
        'partner_requests' => 'SELECT COUNT(*) FROM partners',
        'contact_messages' => 'SELECT COUNT(*) FROM contact_messages',
    ];

    foreach ($summaryQueries as $key => $sql) {
        $stmt = $db->query($sql);
        $stats[$key] = (int) $stmt->fetchColumn();
    }

    $recentApplications = $db->query('SELECT id, full_name, applicant_type, status FROM applications ORDER BY created_at DESC LIMIT 4')->fetchAll();
    $inventory = $db->query('SELECT id, device_type, created_at, "Delivered" AS stage FROM donations ORDER BY created_at DESC LIMIT 4')->fetchAll();
    $recentPartners = $db->query('SELECT id, organisation_name, contact_name, email, partnership_type, created_at FROM partners ORDER BY created_at DESC LIMIT 4')->fetchAll();
    $recentContacts = $db->query('SELECT id, name, email, subject, created_at FROM contact_messages ORDER BY created_at DESC LIMIT 4')->fetchAll();
} catch (Throwable $e) {
    error_log('Dashboard query failed: ' . $e->getMessage());
    $stats = ['devices_in_refurbishment' => 0, 'open_applications' => 0, 'partner_requests' => 0, 'contact_messages' => 0];
    $recentApplications = [];
    $inventory = [];
    $recentPartners = [];
    $recentContacts = [];
}

require_once __DIR__ . '/templates/header.php';
?>
    <section style="background: #eef2f7; padding: 2.5rem 0; border-bottom: 1px solid var(--border);">
      <div class="container">
        <p class="eyebrow" style="color: var(--accent)">Admin</p>
        <h1>Operations overview</h1>
        <p class="muted">Inventory, applications and partners at a glance.</p>
      </div>
    </section>

    <section class="block">
      <div class="container">
        <div class="grid cols-4">
          <div class="card stat">
            <div class="num"><?= esc((string)$stats['devices_in_refurbishment']); ?></div>
            <div class="lbl">Devices in refurbishment</div>
          </div>
          <div class="card stat">
            <div class="num"><?= esc((string)$stats['open_applications']); ?></div>
            <div class="lbl">Open applications</div>
          </div>
          <div class="card stat">
            <div class="num"><?= esc((string)$stats['partner_requests']); ?></div>
            <div class="lbl">Partner requests</div>
          </div>
          <div class="card stat">
            <div class="num"><?= esc((string)$stats['contact_messages']); ?></div>
            <div class="lbl">Contact messages</div>
          </div>
        </div>

        <div class="grid cols-2 mt-4">
          <div class="card" style="padding: 0; overflow-x: auto">
            <h3 style="padding: 1.25rem 1.5rem; margin: 0">Recent applications</h3>
            <table class="table">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Name</th>
                  <th>Type</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($recentApplications)): ?>
                  <tr><td colspan="4">No recent applications available.</td></tr>
                <?php else: ?>
                  <?php foreach ($recentApplications as $application): ?>
                    <tr>
                      <td><code>APP-<?= esc((string)$application['id']); ?></code></td>
                      <td><?= esc((string)$application['full_name']); ?></td>
                      <td><?= esc((string)$application['applicant_type']); ?></td>
                      <td><span class="badge <?= esc((string)$application['status']) === 'pending' ? 'gray' : ''; ?>"><?= esc((string)$application['status']); ?></span></td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
            <div class="card-footer" style="padding: 1rem 1.5rem; background: #f7f9fb; text-align: right;">
              <a class="btn btn-ghost" href="applications">View all applications</a>
            </div>
          </div>

          <div class="card" style="padding: 0; overflow-x: auto">
            <h3 style="padding: 1.25rem 1.5rem; margin: 0">Inventory</h3>
            <table class="table">
              <thead>
                <tr>
                  <th>Device</th>
                  <th>Received</th>
                  <th>Stage</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($inventory)): ?>
                  <tr><td colspan="3">No inventory records available.</td></tr>
                <?php else: ?>
                  <?php foreach ($inventory as $item): ?>
                    <tr>
                      <td><code>DEV-<?= esc((string)$item['id']); ?></code> <?= esc((string)$item['device_type']); ?></td>
                      <td><?= esc((string)$item['created_at']); ?></td>
                      <td><span class="badge"><?= esc((string)$item['stage']); ?></span></td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
            <div class="card-footer" style="padding: 1rem 1.5rem; background: #f7f9fb; text-align: right;">
              <a class="btn btn-ghost" href="donations">View all donations</a>
            </div>
          </div>
        </div>

        <div class="grid cols-2 mt-4">
          <div class="card" style="padding: 0; overflow-x: auto">
            <h3 style="padding: 1.25rem 1.5rem; margin: 0">Recent partner requests</h3>
            <table class="table">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Organisation</th>
                  <th>Contact</th>
                  <th>Type</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($recentPartners)): ?>
                  <tr><td colspan="4">No recent partner requests available.</td></tr>
                <?php else: ?>
                  <?php foreach ($recentPartners as $partner): ?>
                    <tr>
                      <td><code>PR-<?= esc((string)$partner['id']); ?></code></td>
                      <td><?= esc((string)$partner['organisation_name']); ?></td>
                      <td><?= esc((string)$partner['contact_name']); ?><br><?= esc((string)$partner['email']); ?></td>
                      <td><?= esc((string)$partner['partnership_type']); ?></td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
            <div class="card-footer" style="padding: 1rem 1.5rem; background: #f7f9fb; text-align: right;">
              <a class="btn btn-ghost" href="partner-requests">View partner requests</a>
            </div>
          </div>

          <div class="card" style="padding: 0; overflow-x: auto">
            <h3 style="padding: 1.25rem 1.5rem; margin: 0">Recent contact messages</h3>
            <table class="table">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Subject</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($recentContacts)): ?>
                  <tr><td colspan="4">No recent contact messages available.</td></tr>
                <?php else: ?>
                  <?php foreach ($recentContacts as $message): ?>
                    <tr>
                      <td><code>MSG-<?= esc((string)$message['id']); ?></code></td>
                      <td><?= esc((string)$message['name']); ?></td>
                      <td><?= esc((string)$message['email']); ?></td>
                      <td><?= esc((string)$message['subject']); ?></td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
            <div class="card-footer" style="padding: 1rem 1.5rem; background: #f7f9fb; text-align: right;">
              <a class="btn btn-ghost" href="contacts">View contact messages</a>
            </div>
          </div>
        </div>
      </div>
    </section>
<?php
require_once __DIR__ . '/templates/footer.php';
