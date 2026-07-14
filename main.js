// Zee Tech Foundation - shared static site JS
(function () {
  const path = location.pathname.split("/").pop() || "index.html";

  const NAV = [
    { href: "index", label: "Home" },
    { href: "about", label: "About Us" },
    { href: "campaign", label: "Campaign" },
    { href: "donate", label: "Donate" },
    { href: "apply", label: "Apply" },
    { href: "partner", label: "Partner" },
    { href: "impact", label: "Impact" },
    { href: "contact", label: "Contact" },
  ];

  function renderHeader() {
    const el = document.querySelector("[data-header]");
    if (!el) return;
    const links = NAV.map((n) => {
      const isActive = path === n.href;
      return `<a href="${n.href}" class="${isActive ? "active" : ""}" ${
        isActive ? 'aria-current="page"' : ""
      }>${n.label}</a>`;
    }).join("");
    const mobileLinks = NAV.map((n) => {
      const isActive = path === n.href;
      return `<a href="${n.href}" ${
        isActive ? 'aria-current="page"' : ""
      }>${n.label}</a>`;
    }).join("");

    el.innerHTML = `
      <header class="site-header">
        <div class="container nav">
          <a class="brand" href="index">
            <span class="brand-mark">Z</span>
            <span class="brand-name">Zee Tech <span>Foundation</span></span>
          </a>
          <nav class="nav-links">${links}</nav>
          <div class="nav-cta"><a class="btn btn-primary" href="donate">Donate a device</a></div>
          <button class="menu-btn" aria-label="Open menu" aria-expanded="false" aria-controls="mnav">☰</button>
        </div>
        <div id="mnav" class="mobile-nav">
          ${mobileLinks}
          <a href="donate.html" class="btn btn-primary" style="margin-top:.5rem">Donate a device</a>
        </div>
      </header>`;
  }

  function renderFooter() {
    const el = document.querySelector("[data-footer]");
    if (!el) return;
    el.innerHTML = `
      <footer class="site-footer">
        <div class="container">
          <div class="footer-grid">
            <div>
              <div class="brand" style="color:#fff">
                <span class="brand-mark">Z</span>
                <span class="brand-name" style="color:#fff">Zee Tech <span style="color:var(--highlight)">Foundation</span></span>
              </div>
              <p style="margin-top:.75rem;max-width:320px;font-size:.9rem">Bridging the digital divide by putting refurbished technology into the hands of every learner.</p>
            </div>
            <div>
              <h4>Explore</h4>
              <a href="about.php">About</a><br><a href="campaign.php">Campaign</a><br><a href="impact.php">Impact</a><br><a href="contact.php">Contact</a>
            </div>
            <div>
              <h4>Get involved</h4>
              <a href="donate.php">Donate</a><br><a href="apply.php">Apply</a><br><a href="partner.php">Partner</a><br><a href="device-tracking.php">Track a device</a>
            </div>
            <div>
              <h4>Account</h4>
              <a href="login">Sign in</a><br><a href="register">Register</a><br><a href="privacy">Privacy</a><br><a href="terms">Terms</a>
            </div>
          </div>
          <div class="footer-bottom">
            <span>© ${new Date().getFullYear()} Zee Tech Foundation. All rights reserved.</span>
            <span>info@zeetechfoundation.org · +234 810 326 9627</span>
          </div>
        </div>
      </footer>`;
  }

  function toast(msg, type = "success") {
    const t = document.createElement("div");
    t.className = `toast ${type}`;
    t.textContent = msg;
    document.body.appendChild(t);
    requestAnimationFrame(() => t.classList.add("show"));
    setTimeout(() => { t.classList.remove("show"); setTimeout(() => t.remove(), 300); }, 3200);
  }

  function bindForms() {
    document.querySelectorAll("form[data-demo]").forEach((f) => {
      f.addEventListener("submit", (e) => {
        e.preventDefault();
        const terms = f.querySelector('input[name="terms"]');
        if (terms && !terms.checked) {
          toast("Please agree to the Terms & Conditions to continue.", "error");
          terms.focus();
          return;
        }
        const msg = f.dataset.demo || "Submitted (demo)";
        toast(msg, "success");
        f.reset();
      });
    });
  }

  function bindHeaderMenu() {
    const menuBtn = document.querySelector(".menu-btn");
    const mobileNav = document.getElementById("mnav");
    if (!menuBtn || !mobileNav) return;

    const closeMobileNav = () => {
      mobileNav.classList.remove("open");
      menuBtn.setAttribute("aria-expanded", "false");
    };

    menuBtn.addEventListener("click", () => {
      const isOpen = mobileNav.classList.toggle("open");
      menuBtn.setAttribute("aria-expanded", isOpen.toString());
    });

    mobileNav.querySelectorAll("a[href]").forEach((link) => {
      link.addEventListener("click", closeMobileNav);
    });

    window.addEventListener("resize", () => {
      if (window.innerWidth >= 960 && mobileNav.classList.contains("open")) {
        closeMobileNav();
      }
    });
  }

  function initReveal() {
    const els = document.querySelectorAll(".reveal");
    if (!els.length) return;
    if (!("IntersectionObserver" in window)) { els.forEach((e) => e.classList.add("in")); return; }
    const io = new IntersectionObserver((entries) => {
      entries.forEach((en) => {
        if (en.isIntersecting) { en.target.classList.add("in"); io.unobserve(en.target); }
      });
    }, { threshold: 0.12 });
    els.forEach((el) => io.observe(el));
  }

  function initHeroSlider() {
    const slider = document.querySelector("[data-hero-slider]");
    if (!slider) return;
    const slides = slider.querySelectorAll(".hero-slide");
    const dotsWrap = slider.querySelector(".slider-dots");
    let i = 0, timer;
    slides.forEach((_, idx) => {
      const b = document.createElement("button");
      b.setAttribute("aria-label", `Slide ${idx + 1}`);
      b.addEventListener("click", () => go(idx, true));
      dotsWrap.appendChild(b);
    });
    function go(n, manual) {
      i = (n + slides.length) % slides.length;
      slides.forEach((s, idx) => s.classList.toggle("active", idx === i));
      dotsWrap.querySelectorAll("button").forEach((b, idx) => b.classList.toggle("active", idx === i));
      if (manual) restart();
    }
    function restart() { clearInterval(timer); timer = setInterval(() => go(i + 1), 7000); }
    slider.querySelector(".slider-arrow.prev")?.addEventListener("click", () => go(i - 1, true));
    slider.querySelector(".slider-arrow.next")?.addEventListener("click", () => go(i + 1, true));
    go(0); restart();
  }

  document.addEventListener("DOMContentLoaded", () => {
    renderHeader();
    renderFooter();
    bindForms();
    bindHeaderMenu();
    initReveal();
    initHeroSlider();
  });


  window.ztfToast = toast;
})();

const counters = document.querySelectorAll(".num[data-target]");

if (counters.length) {
  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;

        const counter = entry.target;
        const target = parseInt(counter.dataset.target, 10);

        let current = 0;
        const increment = Math.max(1, Math.ceil(target / 120));

        const updateCounter = () => {
          current += increment;

          if (current >= target) {
            current = target;
          }

          counter.textContent = current.toLocaleString();

          if (current < target) {
            requestAnimationFrame(updateCounter);
          } else {
            if (target >= 1000) {
              counter.textContent = target.toLocaleString() + "+";
            }
          }
        };

        updateCounter();
        observer.unobserve(counter);
      });
    },
    {
      threshold: 0.5,
    }
  );

  counters.forEach((counter) => observer.observe(counter));
}
