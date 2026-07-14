<?php
declare(strict_types=1);

require_once __DIR__ . '/init.php';

$pageTitle = 'Impact - Zee Tech Foundation';
$pageDescription = 'Stories, numbers and testimonials from the communities served by Zee Tech Foundation.';
$pageRobots = 'index, follow';

require_once __DIR__ . '/templates/header.php';
?>
      <section class="block">
        <div class="container">
          <div class="section-head center reveal">
            <p class="eyebrow">Our Impact</p>
            <h1>Every Device Creates New Opportunities</h1>
            <p>
              Behind every donated device is a learner, teacher or community
              gaining access to education, skills and opportunity.
            </p>
          </div>
        </div>
      </section>

      <!-- Statistics -->
      <section class="block alt">
        <div class="container">
          <div class="grid cols-4">
            <div class="card stat reveal">
                <div class="num" data-target="2400">0</div>
                <div class="lbl">Devices Donated</div>
            </div>

            <div class="card stat reveal delay-1">
                <div class="num" data-target="1850">0</div>
                <div class="lbl">Beneficiaries Reached</div>
            </div>

            <div class="card stat reveal delay-2">
                <div class="num" data-target="60">0</div>
                <div class="lbl">Partner Organisations</div>
            </div>

            <div class="card stat reveal delay-3">
                <div class="num" data-target="18">0</div>
                <div class="lbl">Communities Served</div>
            </div>
        </div>
      </section>

      <!-- How we measure -->
      <section class="block">
        <div class="container">
          <div class="section-head center reveal">
            <h2>How We Measure Impact</h2>
            <p>
              We track every stage of the campaign to ensure donations create
              meaningful and lasting change.
            </p>
          </div>

          <div class="grid cols-3">
            <div class="card reveal">
              <h3>Track</h3>
              <p>
                Every donated device is recorded, refurbished and monitored.
              </p>
            </div>

            <div class="card reveal delay-1">
              <h3>Measure</h3>
              <p>
                We monitor beneficiaries, schools reached and community
                outcomes.
              </p>
            </div>

            <div class="card reveal delay-2">
              <h3>Share</h3>
              <p>We publish updates, impact stories and campaign results.</p>
            </div>
          </div>
        </div>
      </section>

      <!-- Stories -->
      <section class="block alt">
        <div class="container">
          <div class="section-head center reveal">
            <h2>Stories Behind the Numbers</h2>
          </div>

          <div class="grid cols-3">
            <div class="card reveal">
              <h3>Teachers</h3>
              <p>
                Teachers gain modern tools to prepare lessons and improve
                classroom learning.
              </p>
            </div>

            <div class="card reveal delay-1">
              <h3>Students</h3>
              <p>
                Learners access online resources, complete assignments and build
                digital skills.
              </p>
            </div>

            <div class="card reveal delay-2">
              <h3>Communities</h3>
              <p>
                Schools and community centres expand access to technology and
                digital opportunities.
              </p>
            </div>
          </div>
        </div>
      </section>

      <!-- Image + Future -->
      <section class="block">
        <div class="container">
          <div class="grid cols-2" style="align-items: center; gap: 3rem">
            <img
              src="images/handover.jpg"
              alt="Device handover"
              class="section-image reveal"
            />

            <div class="reveal delay-1">
              <h2>Looking Ahead</h2>

              <p>
                Our goal is to reach more schools, strengthen partnerships and
                give thousands more learners access to technology across
                Nigeria.
              </p>
            </div>
          </div>
        </div>
      </section>
<?php
require_once __DIR__ . '/templates/footer.php';
