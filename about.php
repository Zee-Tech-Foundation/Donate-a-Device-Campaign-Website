<?php
declare(strict_types=1);

require_once __DIR__ . '/init.php';

$pageTitle = 'About Us - Zee Tech Foundation';
$pageDescription = 'Learn about Zee Tech Foundation - our vision, mission and the team working to close the digital divide.';
$pageRobots = 'index, follow';

require_once __DIR__ . '/templates/header.php';
?>
      <section class="page-hero">
        <div class="container">
          <p class="eyebrow">About Zee Tech Foundation</p>

          <h1>Creating Opportunities Through Technology</h1>

          <p>
            Zee Tech Foundation is committed to building a digitally inclusive
            future by giving unused technology a second life. Through the
            <strong>Donate a Device – Change a Future</strong> campaign, we
            collect, refurbish, and redistribute digital devices to people and
            institutions where they create lasting educational and social
            impact.
          </p>
        </div>
      </section>

      <!-- ==========================================
           OUR STORY
      =========================================== -->

      <section class="block">
        <div class="container">
          <div class="grid cols-2" style="align-items: center; gap: 3rem">
            <div class="reveal">
              <img
                src="images/about-story.jpg"
                alt="Students using donated laptops"
                class="section-image"
              />
            </div>

            <div class="reveal delay-1">
              <p class="eyebrow">Our Story</p>

              <h2>About the Donate a Device – Change a Future Campaign</h2>

              <p>
                The Donate a Device – Change a Future Campaign is one of Zee
                Tech Foundation's flagship digital inclusion initiatives. It was
                created to address a growing challenge: while technology is
                becoming essential for education and employment, many people
                still lack access to the devices needed to participate.
              </p>

              <p>
                At the same time, homes, businesses, schools, and institutions
                regularly replace computers and digital equipment that remain
                functional or can be restored with minimal repairs. Rather than
                allowing these devices to become electronic waste, we refurbish
                and redistribute them to individuals and institutions where they
                can create meaningful educational and social impact.
              </p>

              <p>
                Every donated device becomes an investment in someone's future.
                Every refurbished computer creates new opportunities for
                learning, innovation, and growth. Every act of generosity helps
                build a more inclusive digital society.
              </p>
            </div>
          </div>
        </div>
      </section>
      <!-- ==========================================
           OUR VALUES
      =========================================== -->

      <section class="block alt">
        <div class="container">
          <div class="section-head center reveal">
            <p class="eyebrow">Our Values</p>

            <h2>The Principles That Guide Everything We Do</h2>

            <p class="muted">
              Every decision we make is guided by a commitment to creating
              meaningful, sustainable, and lasting impact through technology.
            </p>
          </div>

          <div class="grid cols-3">
            <!-- Inclusion -->

            <div class="card reveal">
              <h3>Inclusion</h3>

              <p>
                We believe everyone deserves equal access to technology and the
                opportunities it creates, regardless of their background,
                gender, income, or physical ability.
              </p>
            </div>

            <!-- Integrity -->

            <div class="card reveal delay-1">
              <h3>Integrity</h3>

              <p>
                We are committed to transparency, accountability, and the
                responsible stewardship of every device, donation, and
                partnership entrusted to us.
              </p>
            </div>

            <!-- Innovation -->

            <div class="card reveal delay-2">
              <h3>Innovation</h3>

              <p>
                We embrace practical and creative solutions that solve real
                community challenges and improve lives through technology.
              </p>
            </div>

            <!-- Collaboration -->

            <div class="card reveal">
              <h3>Collaboration</h3>

              <p>
                Lasting impact is built through strong partnerships with
                individuals, businesses, schools, governments, and development
                organizations working toward a shared goal.
              </p>
            </div>

            <!-- Sustainability -->

            <div class="card reveal delay-1">
              <h3>Sustainability</h3>

              <p>
                We extend the life of valuable technology through responsible
                refurbishment, reducing electronic waste while expanding digital
                access.
              </p>
            </div>

            <!-- Empowerment -->

            <div class="card reveal delay-2">
              <h3>Empowerment</h3>

              <p>
                We do more than provide devices. We equip people with the
                confidence, knowledge, and digital skills needed to create
                better futures.
              </p>
            </div>
          </div>
        </div>
      </section>
      <!-- ==========================================
           WHY OUR WORK MATTERS
      =========================================== -->

      <section class="block">
        <div class="container">
          <div class="grid cols-2" style="align-items: center; gap: 3rem">
            <!-- Text -->

            <div class="reveal">
              <p class="eyebrow">Why Our Work Matters</p>

              <h2>Expanding Access to Opportunity</h2>

              <p>
                The digital divide is about more than access to devices. It is
                about access to education, employment, entrepreneurship, and
                opportunity.
              </p>

              <p>
                Without technology, students struggle to complete assignments,
                teachers miss opportunities for professional development, job
                seekers face barriers to employment, and entrepreneurs find it
                harder to grow their businesses.
              </p>

              <p>
                By expanding digital access, we help individuals develop new
                skills, strengthen educational institutions, encourage
                innovation, and create pathways to long-term community
                development.
              </p>

              <a href="campaign" class="btn btn-dark mt-3">
                Learn About the Campaign
              </a>
            </div>

            <!-- Image -->

            <div class="reveal delay-1">
              <img
                src="images/refurbish.jpg"
                alt="Students learning with donated digital devices"
                class="section-image"
              />
            </div>
          </div>
        </div>
      </section>

      <!-- ==========================================
           TRANSPARENCY & ACCOUNTABILITY
      =========================================== -->

      <section class="block alt">
        <div class="container">
          <div class="grid cols-2" style="align-items: center; gap: 3rem">
            <!-- Text -->

            <div class="reveal">
              <p class="eyebrow">Transparency & Accountability</p>

              <h2>Responsible Stewardship of Every Donation</h2>

              <p>
                Trust is the foundation of every successful partnership. We are
                committed to ensuring every donated device, financial
                contribution, and partnership is managed responsibly,
                transparently, and ethically.
              </p>

              <div class="check-list">
                <div class="check-item">
                  ✓ Every donated device is documented.
                </div>

                <div class="check-item">
                  ✓ Devices are assessed and refurbished using established
                  technical processes.
                </div>

                <div class="check-item">
                  ✓ Beneficiaries are selected through transparent procedures.
                </div>

                <div class="check-item">
                  ✓ Device distribution and utilization are monitored.
                </div>

                <div class="check-item">
                  ✓ Impact reports and success stories are shared regularly.
                </div>

                <div class="check-item">
                  ✓ Programs are continuously evaluated for greater impact.
                </div>
              </div>

              <a href="impact" class="btn btn-primary mt-3">
                View Our Impact
              </a>
            </div>

            <!-- Image -->

            <div class="reveal delay-1">
              <img
                src="images/transparency.jpg"
                alt="Team documenting donated devices"
                class="section-image"
              />
            </div>
          </div>
        </div>
      </section>
      <!-- ==========================================
           OUR TEAM
      =========================================== -->

      <section class="block">
        <div class="container">
          <div class="section-head center reveal">
            <p class="eyebrow">Meet Our Team</p>

            <h2>The People Behind the Mission</h2>

            <p class="muted">
              Our team is passionate about expanding digital inclusion and
              creating opportunities through technology. Together, we work to
              ensure every donated device reaches the people and communities
              where it can make the greatest impact.
            </p>
          </div>

          <div class="grid cols-4">
            <div class="card team-card reveal">
              <img
                class="team-img"
                src="images/team-1.jpg"
                alt="Zubairu Jubril - Executive Director"
              />

              <h3>Zubairu Jubril</h3>

              <p class="muted">Founder/Executive Director</p>
            </div>

            <div class="card team-card reveal delay-1">
              <img
                class="team-img"
                src="images/team-2.jpg"
                alt="Dorcas Daniel - Operations Lead"
              />

              <h3>Dorcas Daniel</h3>

              <p class="muted">Operations Lead</p>
            </div>

            <div class="card team-card reveal delay-2">
              <img
                class="team-img"
                src="images/team-3.jpg"
                alt="Daniella Ochi - Refurbishment Lead"
              />

              <h3>Daniella Ochi</h3>

              <p class="muted">Refurbishment Lead</p>
            </div>

            <div class="card team-card reveal delay-3">
              <img
                class="team-img"
                src="images/team-4.jpg"
                alt="Adebisi Covenant - Partnerships"
              />

              <h3>Adebisi Covenant</h3>

              <p class="muted">Developer</p>
            </div>
          </div>
        </div>
      </section>

      <!-- ==========================================
           JOIN OUR MISSION
      =========================================== -->

      <section class="block alt">
        <div class="container">
          <div class="grid cols-2" style="align-items: center; gap: 3rem">
            <div class="reveal">
              <p class="eyebrow">Join Our Mission</p>

              <h2>Together, We Can Build a More Inclusive Digital Future</h2>

              <p>
                Building a digitally inclusive future requires collective
                action. Whether you are an individual with an unused laptop, a
                business upgrading its IT infrastructure, an educational
                institution supporting learners, or a development partner
                expanding digital access, there is a place for you in this
                movement.
              </p>

              <p>
                Together, we can transform unused technology into opportunity,
                reduce electronic waste, strengthen education, empower
                communities, and create lasting impact for generations to come.
              </p>

              <p>
                At Zee Tech Foundation, we do more than distribute devices—we
                create opportunities, inspire innovation, and help build a
                future where everyone has the chance to learn, connect, and
                succeed.
              </p>
            </div>

            <div class="reveal delay-1">
              <img
                src="images/handover.jpg"
                alt="Community members receiving donated laptops"
                class="section-image"
              />
            </div>
          </div>
        </div>
      </section>

      <!-- ==========================================
           FINAL CALL TO ACTION
      =========================================== -->

      <section class="block">
        <div class="container">
          <div class="cta-banner reveal">
            <p class="eyebrow" style="color: rgba(255, 255, 255, 0.8)">
              Get Involved
            </p>

            <h2>Every Device Has the Power to Transform a Life</h2>

            <p>
              Join individuals, businesses, schools, and organizations across
              Nigeria in creating opportunities through technology. Together, we
              can bridge the digital divide and ensure more people have the
              tools they need to learn, work, innovate, and thrive.
            </p>

            <div
              style="
                display: flex;
                justify-content: center;
                gap: 1rem;
                flex-wrap: wrap;
                margin-top: 2rem;
              "
            >
              <a href="donate" class="btn btn-primary">
                Donate a Device
              </a>

              <a href="partner" class="btn btn-outline">
                Become a Partner
              </a>

              <a href="campaign" class="btn btn-dark"> Learn More </a>
            </div>
          </div>
        </div>
      </section>
<?php
require_once __DIR__ . '/templates/footer.php';
