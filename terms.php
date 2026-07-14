<?php
declare(strict_types=1);

require_once __DIR__ . '/init.php';

$pageTitle = 'Terms & Conditions - Zee Tech Foundation';
$pageDescription = 'Terms of use for Zee Tech Foundation site visitors, donors, applicants and partners.';
$pageRobots = 'noindex, follow';

require_once __DIR__ . '/templates/header.php';
?>
      <section class="page-hero">
        <div class="container">
          <p class="eyebrow">Legal</p>
          <h1>Terms & Conditions</h1>
          <p>Last updated: July 2026</p>
        </div>
      </section>
      <section class="block">
        <div class="container prose">
          <h2>Use of this site</h2>
          <p>
            By using this site you agree to these terms. If you don't agree,
            please don't use the site.
          </p>
          <h2>Donations</h2>
          <p>
            By donating a device you confirm you own it and have removed
            personal data, or have given us permission to wipe it.
          </p>
          <h2>Applications</h2>
          <p>
            Applications are reviewed based on need, eligibility and inventory.
            Submission is not a guarantee.
          </p>
          <h2>Content</h2>
          <p>All content is © Zee Tech Foundation unless otherwise noted.</p>
          <h2>Contact</h2>
          <p><a href="mailto:info@zeetechfoundation.org">info@zeetechfoundation.org</a></p>
        </div>
      </section>
<?php
require_once __DIR__ . '/templates/footer.php';
