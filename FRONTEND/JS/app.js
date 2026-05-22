const mainContent = document.getElementById("mainContent");
const heroEnterBtn = document.getElementById("heroEnterBtn");
const heroVehiclesBtn = document.getElementById("heroVehiclesBtn");
const scrollToPanelBtn = document.getElementById("scrollToPanelBtn");

const scrollToPanel = () => {
  if (!mainContent) return;

  mainContent.scrollIntoView({
    behavior: "smooth",
    block: "start",
  });
};

heroEnterBtn?.addEventListener("click", () => {
  scrollToPanel();
  activateTab("tab-clientes");
});

heroVehiclesBtn?.addEventListener("click", () => {
  scrollToPanel();
  activateTab("tab-vehiculos");
});

scrollToPanelBtn?.addEventListener("click", () => {
  scrollToPanel();
});

document.addEventListener("DOMContentLoaded", async () => {
  activateTab("tab-clientes");
  await consultarClientes();
  await consultarVehiculos();
});