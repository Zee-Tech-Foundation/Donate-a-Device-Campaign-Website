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