const tabButtons = Array.from(document.querySelectorAll(".tab-btn[role='tab']"));
const tabPanels = Array.from(document.querySelectorAll(".tab-view[role='tabpanel']"));

const activateTab = (tabId) => {
  tabButtons.forEach((button) => {
    const isActive = button.id === tabId;
    const panelId = button.getAttribute("aria-controls");
    const panel = document.getElementById(panelId);

    button.classList.toggle("active", isActive);
    button.setAttribute("aria-selected", String(isActive));
    button.setAttribute("tabindex", isActive ? "0" : "-1");

    if (panel) {
      panel.classList.toggle("active", isActive);
      panel.hidden = !isActive;
    }
  });
};

tabButtons.forEach((button, index) => {
  button.addEventListener("click", () => {
    activateTab(button.id);
  });

  button.addEventListener("keydown", (event) => {
    let targetIndex = index;

    if (event.key === "ArrowRight" || event.key === "ArrowDown") {
      event.preventDefault();
      targetIndex = (index + 1) % tabButtons.length;
      tabButtons[targetIndex].focus();
      activateTab(tabButtons[targetIndex].id);
    }

    if (event.key === "ArrowLeft" || event.key === "ArrowUp") {
      event.preventDefault();
      targetIndex = (index - 1 + tabButtons.length) % tabButtons.length;
      tabButtons[targetIndex].focus();
      activateTab(tabButtons[targetIndex].id);
    }

    if (event.key === "Home") {
      event.preventDefault();
      tabButtons[0].focus();
      activateTab(tabButtons[0].id);
    }

    if (event.key === "End") {
      event.preventDefault();
      tabButtons[tabButtons.length - 1].focus();
      activateTab(tabButtons[tabButtons.length - 1].id);
    }
  });
});