// js/components/Dashboard.js
export class Dashboard {
  constructor({ clienteModule, vehiculoModule, reservaModule }) {
    this.clienteModule = clienteModule;
    this.vehiculoModule = vehiculoModule;
    this.reservaModule = reservaModule;

    this.kpiClientes = document.getElementById("kpiClientes");
    this.kpiVehiculos = document.getElementById("kpiVehiculos");
    this.kpiReservasActivas = document.getElementById("kpiReservasActivas");
  }

  async update() {
    // Asume que los módulos ya cargaron datos
    this.kpiClientes.textContent = this.clienteModule.clientes.length;
    this.kpiVehiculos.textContent = this.vehiculoModule.vehiculos.length;
    this.kpiReservasActivas.textContent = this.reservaModule.reservas.filter(
      (r) => r.estado === "activa"
    ).length;
  }
}