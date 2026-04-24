document.addEventListener("DOMContentLoaded", () => {
  const nav = document.querySelector(".directory-section-nav");
  if (!nav) return;

  const links = Array.from(nav.querySelectorAll('a[href^="#"]'));
  if (!links.length) return;

  const sections = links
    .map((link) => {
      const id = link.getAttribute("href");
      const section = document.querySelector(id);
      return section ? { link, section, id } : null;
    })
    .filter(Boolean);

  if (!sections.length) return;

  const setActive = (id) => {
    sections.forEach(({ link, id: sectionId }) => {
      link.classList.toggle("active", sectionId === id);
    });
  };

  // click feedback immediately
  sections.forEach(({ link, id }) => {
    link.addEventListener("click", () => {
      setActive(id);
    });
  });

  const observer = new IntersectionObserver(
    (entries) => {
      const visible = entries
        .filter((entry) => entry.isIntersecting)
        .sort((a, b) => b.intersectionRatio - a.intersectionRatio);

      if (visible.length) {
        const activeId = `#${visible[0].target.id}`;
        setActive(activeId);
      }
    },
    {
      root: null,
      rootMargin: "-140px 0px -45% 0px",
      threshold: [0.15, 0.3, 0.5, 0.7],
    }
  );

  sections.forEach(({ section }) => observer.observe(section));

  // initial state
  const hash = window.location.hash;
  if (hash && sections.some((item) => item.id === hash)) {
    setActive(hash);
  } else {
    setActive("#overview");
  }
});
