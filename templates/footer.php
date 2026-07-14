<?php
if (!defined('SITE_NAME')) {
    exit('Footer could not be loaded.');
}
?>
    <footer class="site-footer">
      <div class="container">
        <div class="footer-grid">
          <div>
            <div class="brand" style="color:#fff">
              <img class="brand-logo" src="images/logo2.png" width="150px" alt="Zee Tech Foundation logo" />
              <!-- <span class="brand-mark">Z</span> -->
              <!-- <span class="brand-name" style="color:#fff">Zee Tech <span style="color:var(--highlight)">Foundation</span></span> -->
            </div>
            <p style="margin-top:.75rem;max-width:320px;font-size:.9rem">Bridging the digital divide by putting refurbished technology into the hands of every learner.</p>
          </div>
          <div>
            <h4>Explore</h4>
            <a href="about">About</a><br><a href="campaign">Campaign</a><br><a href="impact">Impact</a><br><a href="contact">Contact</a>
          </div>
          <div>
            <h4>Get involved</h4>
            <a href="donate">Donate</a><br><a href="apply">Apply</a><br><a href="partner">Partner</a><br><a href="device-tracking">Track a device</a>
          </div>
          <div>
            <h4>Account</h4>
            <a href="login">Sign in</a><br><a href="register">Register</a><br><a href="privacy">Privacy</a><br><a href="terms">Terms</a>
          </div>
        </div>
        <div class="footer-bottom">
          <span>© <?= date('Y'); ?> Zee Tech Foundation. All rights reserved.</span>
          <span><?= esc(SITE_EMAIL); ?> · +234 810 326 9627</span>
        </div>
      </div>
    </footer>
    <script src="main.js"></script>
  </body>
</html>
