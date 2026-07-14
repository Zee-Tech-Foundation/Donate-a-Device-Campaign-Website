<?php
declare(strict_types=1);

require_once __DIR__ . '/init.php';

$pageTitle = 'Privacy Policy - Zee Tech Foundation';
$pageDescription = 'How Zee Tech Foundation collects and handles your data.';
$pageRobots = 'noindex, follow';

require_once __DIR__ . '/templates/header.php';
?>
      <section class="page-hero">
        <div class="container">
          <p class="eyebrow">Legal</p>
          <h1>Privacy Policy</h1>
          <p>Last updated: July 2026</p>
        </div>
      </section>
      <section class="block">
        <div class="container prose">
          <h2>What we collect</h2>
          <p>
            We collect information you provide when you donate, apply, or
            contact us - name, email, phone, and any details you share.
          </p>
          <h2>How we use it</h2>
          <p>
            To process donations and applications, to coordinate device
            pickup/delivery, and to keep you updated on the programme.
          </p>
          <h2>Device data</h2>
          <p>
            All donated devices undergo a certified data wipe before
            refurbishment. We do not retain any data from donated devices.
          </p>
          <h2>Sharing</h2>
          <p>
            We never sell your data. Limited details may be shared with
            logistics partners to fulfil donations and deliveries.
          </p>
          <h2>Contact</h2>
          <p>
            Email <a href="mailto:info@zeetechfoundation.org">info@zeetechfoundation.org</a> with
            any questions.
          </p>
        </div>
      </section>
<?php
require_once __DIR__ . '/templates/footer.php';
