// js/app.js
import { Modal } from "./components/Modal.js";
import { Tabs } from "./components/Tabs.js";
import { ClienteModule } from "./modules/clientes/ClienteModule.js";
import { VehiculoModule } from "./modules/vehiculos/VehiculoModule.js";
import { ReservaModule } from "./modules/reservas/ReservaModule.js";
import { Dashboard } from "./components/Dashboard.js";

const modal = new Modal(document.getElementById("modal"));

// Instanciar módulos
const clienteModule = new ClienteModule({ modal });
const vehiculoModule = new VehiculoModule({ modal });
const reservaModule = new ReservaModule({
  modal,
  clienteModule,
  vehiculoModule,
});

// Dashboard (usa los arrays de los módulos)
const dashboard = new Dashboard({
  clienteModule,
  vehiculoModule,
  reservaModule,
});

// Tabs (pestañas Clientes / Vehículos / Reservas)
const tabs = new Tabs(
  document.querySelector(".segment-tabs"),
  document.querySelectorAll(".module.tab-panel")
);

// Botones que abren una pestaña específica (ej. "Ver vehículos")
document.addEventListener("click", (e) => {
  const btn = e.target.closest("[data-open-tab]");
  if (!btn) return;
  const tabName = btn.getAttribute("data-open-tab");
  if (tabName) {
    tabs.activate(tabName);
  }
});

// Avisar al módulo de reservas cuando cambian clientes/vehículos
clienteModule.onChange = () => reservaModule.syncFromModules();
vehiculoModule.onChange = () => reservaModule.syncFromModules();

window.addEventListener("DOMContentLoaded", async () => {
  try {
    // Cargamos datos iniciales
    await Promise.all([
      clienteModule.loadClientes(),
      vehiculoModule.loadVehiculos(),
      reservaModule.loadReservas(),
    ]);

    // Llenar selects de reservas con listas cargadas
    reservaModule.syncFromModules();

    // Actualizar KPIs
    await dashboard.update();
  } catch (err) {
    console.error(err);
    modal.show("No se pudieron cargar los datos iniciales", "Error");
  }
});