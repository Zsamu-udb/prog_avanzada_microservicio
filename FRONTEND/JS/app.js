window.addEventListener("DOMContentLoaded", async function () {
  const modal = new Modal(document.getElementById("appModal"));

  const clienteModule = new ClienteModule({ modal: modal });
  const vehiculoModule = new VehiculoModule({ modal: modal });
  const reservaModule = new ReservaModule({
    modal: modal,
    clienteModule: clienteModule,
    vehiculoModule: vehiculoModule
  });

  const dashboard = new Dashboard({
    clienteModule: clienteModule,
    vehiculoModule: vehiculoModule,
    reservaModule: reservaModule
  });

  const tabs = new Tabs(
    document.getElementById("tabsNav"),
    document.querySelectorAll(".tab-panel")
  );

  function refreshAllIndicators() {
    reservaModule.syncFromModules();
    dashboard.update();
  }

  clienteModule.onChange = function () {
    refreshAllIndicators();
  };

  vehiculoModule.onChange = function () {
    refreshAllIndicators();
  };

  reservaModule.onChange = function () {
    refreshAllIndicators();
  };

  document.querySelectorAll(".hero__actions .tab").forEach(function (btn) {
    btn.addEventListener("click", function () {
      const tabName = btn.getAttribute("data-tab");
      if (!tabName) return;

      tabs.activate(tabName);

      const panel = document.getElementById("tab-" + tabName);
      if (panel) {
        panel.scrollIntoView({ behavior: "smooth", block: "start" });
      }
    });
  });

  try {
    await Promise.all([
      clienteModule.loadClientes(),
      vehiculoModule.loadVehiculos(),
      reservaModule.loadReservas()
    ]);

    refreshAllIndicators();
  } catch (err) {
    console.error(err);
    modal.show("No se pudieron cargar los datos iniciales", "Error");
  }
});