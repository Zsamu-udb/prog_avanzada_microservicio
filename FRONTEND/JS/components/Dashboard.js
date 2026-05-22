class Dashboard {
  constructor(options) {
    this.clienteModule = options.clienteModule;
    this.vehiculoModule = options.vehiculoModule;
    this.reservaModule = options.reservaModule;

    this.kpiClientes = document.getElementById("kpiClientes");
    this.kpiVehiculos = document.getElementById("kpiVehiculos");
    this.kpiReservasActivas = document.getElementById("kpiReservasActivas");
  }

  update() {
    this.kpiClientes.textContent = this.clienteModule.clientes.length;
    this.kpiVehiculos.textContent = this.vehiculoModule.vehiculos.length;
    this.kpiReservasActivas.textContent = this.reservaModule.reservas.filter(
      (r) => r.estado === "activa"
    ).length;
  }
}