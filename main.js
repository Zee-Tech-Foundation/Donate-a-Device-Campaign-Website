  function initHeroSlider() {
    const slides = document.querySelectorAll(".slide");
    const dots = document.querySelectorAll(".dot");
    const nextBtn = document.querySelector(".next");
    const prevBtn = document.querySelector(".prev");

    // Don't run on pages without the slider
    if (!slides.length) return;

    let current = 0;

    function showSlide(index) {
      slides.forEach((slide) => slide.classList.remove("active"));
      dots.forEach((dot) => dot.classList.remove("active"));

      slides[index].classList.add("active");

      if (dots[index]) {
        dots[index].classList.add("active");
      }

      current = index;
    }

    nextBtn?.addEventListener("click", () => {
      let next = current + 1;
      if (next >= slides.length) next = 0;
      showSlide(next);
    });

    prevBtn?.addEventListener("click", () => {
      let prev = current - 1;
      if (prev < 0) prev = slides.length - 1;
      showSlide(prev);
    });

    dots.forEach((dot, index) => {
      dot.addEventListener("click", () => showSlide(index));
    });

    setInterval(() => {
      let next = current + 1;
      if (next >= slides.length) next = 0;
      showSlide(next);
    }, 6000);
  }

// Animated Impact Counters
const counters = document.querySelectorAll(".num[data-target]");

if (counters.length) {
  const animateCounter = (counter) => {
    const target = Number(counter.dataset.target);
    const duration = 2000;
    const startTime = performance.now();

    function update(currentTime) {
      const progress = Math.min((currentTime - startTime) / duration, 1);
      const value = Math.floor(progress * target);

      counter.textContent = value.toLocaleString();

      if (progress < 1) {
        requestAnimationFrame(update);
      } else {
        counter.textContent =
          target.toLocaleString() + (target >= 1000 ? "+" : "");
      }
    }

    requestAnimationFrame(update);
  };

  const observer = new IntersectionObserver(
    (entries, obs) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          animateCounter(entry.target);
          obs.unobserve(entry.target);
        }
      });
    },
    { threshold: 0.5 }
  );

  counters.forEach((counter) => observer.observe(counter));
}