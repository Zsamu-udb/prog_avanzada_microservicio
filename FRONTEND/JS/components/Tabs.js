// js/components/Tabs.js
export class Tabs {
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
    // Botones
    this.navEl.querySelectorAll(".tab").forEach((btn) => {
      btn.classList.toggle("active", btn.dataset.tab === tabName);
    });

    // Paneles
    this.panels.forEach((panel) => {
      panel.classList.toggle("active", panel.id === `tab-${tabName}`);
    });
  }
}