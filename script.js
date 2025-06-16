document.addEventListener("DOMContentLoaded", () => {
  const sections = document.querySelectorAll("section");

  const observer = new IntersectionObserver((entries, observer) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add("visible");
        entry.target.classList.remove("hidden");
        observer.unobserve(entry.target); // Animer une seule fois
      }
    });
  }, {
    threshold: 0.1
  });

  sections.forEach(section => {
    section.classList.add("hidden");
    observer.observe(section);
  });
});
