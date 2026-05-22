class Tabs {
  constructor(navEl, panels) {
    this.navEl = navEl;
    this.panels = Array.from(panels);

    this.navEl.addEventListener("click", (e) => {
      const btn = e.target.closest(".tab");
      if (!btn) return;
      this.activate(btn.dataset.tab);
    });
  }

  activate(tabName) {
    document.querySelectorAll(".tab").forEach((btn) => {
      btn.classList.toggle("active", btn.dataset.tab === tabName);
    });

    this.panels.forEach((panel) => {
      panel.classList.toggle("active", panel.id === `tab-${tabName}`);
    });

    const target = document.getElementById(`tab-${tabName}`);
    if (target) {
      target.scrollIntoView({ behavior: "smooth", block: "start" });
    }
  }
}