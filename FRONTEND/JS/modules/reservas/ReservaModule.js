// js/modules/reservas/ReservaModule.js
import { ReservaService } from "../../services/ReservaService.js";
import { Validator } from "../../core/validator.js";

export class ReservaModule {
  constructor({ modal, clienteModule, vehiculoModule }) {
    this.modal = modal;
    this.service = new ReservaService();
    this.reservas = [];
    this.clienteModule = clienteModule;
    this.vehiculoModule = vehiculoModule;

    this.form = document.getElementById("reservaForm");
    this.tableBody = document.querySelector("#reservasTabla tbody");
    this.btnRefrescar = document.getElementById("btnRefrescarReservas");

    this.form.addEventListener("submit", (e) => this.onSubmit(e));
    this.form.addEventListener("reset", () => this.onReset());
    this.btnRefrescar.addEventListener("click", () => this.loadReservas());
  }

  // Llenar selects de clientes y vehículos usando los otros módulos
  syncFromModules() {
    const clienteSelect = this.form.cliente_id;
    const vehiculoSelect = this.form.vehiculo_id;

    clienteSelect.innerHTML = "";
    this.clienteModule.clientes.forEach((c) => {
      const opt = document.createElement("option");
      opt.value = c.id;
      opt.textContent = c.nombre;
      clienteSelect.appendChild(opt);
    });

    vehiculoSelect.innerHTML = "";
    this.vehiculoModule.vehiculos.forEach((v) => {
      const opt = document.createElement("option");
      opt.value = v.id;
      opt.textContent = `${v.marca} ${v.modelo}`;
      vehiculoSelect.appendChild(opt);
    });
  }

  async loadReservas() {
    const data = await this.service.getAll();
    this.reservas = data || [];
    this.renderTable();
  }

  renderTable() {
    this.tableBody.innerHTML = "";

    if (this.reservas.length === 0) {
      const tr = document.createElement("tr");
      tr.innerHTML =
        '<td colspan="6" class="table__empty">Sin reservas registradas</td>';
      this.tableBody.appendChild(tr);
      return;
    }

    this.reservas.forEach((r) => {
      const clienteNombre = r.cliente?.nombre || r.cliente_id;
      const vehiculoNombre = r.vehiculo
        ? `${r.vehiculo.marca} ${r.vehiculo.modelo}`
        : r.vehiculo_id;

      const tr = document.createElement("tr");
      tr.innerHTML = `
        <td>${clienteNombre}</td>
        <td>${vehiculoNombre}</td>
        <td>${r.fecha_inicio}</td>
        <td>${r.fecha_fin}</td>
        <td>${r.estado}</td>
        <td>
          <button class="btn-soft" data-action="edit">Editar</button>
          <button class="btn-soft" data-action="completar">Completar</button>
          <button class="btn-soft" data-action="cancelar">Cancelar</button>
          <button class="btn-soft" data-action="delete">Borrar</button>
        </td>
      `;

      tr
        .querySelector('[data-action="edit"]')
        .addEventListener("click", () => this.fillForm(r));
      tr
        .querySelector('[data-action="completar"]')
        .addEventListener("click", () => this.completar(r.id));
      tr
        .querySelector('[data-action="cancelar"]')
        .addEventListener("click", () => this.cancelar(r.id));
      tr
        .querySelector('[data-action="delete"]')
        .addEventListener("click", () => this.delete(r.id));

      this.tableBody.appendChild(tr);
    });
  }

  fillForm(r) {
    this.form.id.value = r.id;
    this.form.cliente_id.value = r.cliente_id;
    this.form.vehiculo_id.value = r.vehiculo_id;
    this.form.fecha_inicio.value = r.fecha_inicio;
    this.form.fecha_fin.value = r.fecha_fin;
    this.form.estado.value = r.estado;
  }

  clearErrors() {
    this.form
      .querySelectorAll(".field__error")
      .forEach((span) => (span.textContent = ""));
  }

  setError(field, message) {
    const span = this.form.querySelector(`[data-error-for="${field}"]`);
    if (span) span.textContent = message;
  }

  getFormData() {
    return {
      cliente_id: this.form.cliente_id.value,
      vehiculo_id: this.form.vehiculo_id.value,
      fecha_inicio: this.form.fecha_inicio.value,
      fecha_fin: this.form.fecha_fin.value,
      estado: this.form.estado.value,
    };
  }

  validate(data) {
    this.clearErrors();
    let ok = true;

    if (!Validator.required(data.cliente_id)) {
      this.setError("cliente_id", "El cliente es obligatorio");
      ok = false;
    }
    if (!Validator.required(data.vehiculo_id)) {
      this.setError("vehiculo_id", "El vehículo es obligatorio");
      ok = false;
    }
    if (!Validator.required(data.fecha_inicio)) {
      this.setError("fecha_inicio", "La fecha de inicio es obligatoria");
      ok = false;
    }
    if (!Validator.required(data.fecha_fin)) {
      this.setError("fecha_fin", "La fecha fin es obligatoria");
      ok = false;
    }
    if (data.fecha_inicio && data.fecha_fin && data.fecha_fin < data.fecha_inicio) {
      this.setError("fecha_fin", "La fecha fin no puede ser menor a la de inicio");
      ok = false;
    }

    return ok;
  }

  async onSubmit(e) {
    e.preventDefault();
    const id = this.form.id.value;
    const data = this.getFormData();

    if (!this.validate(data)) return;

    try {
      if (id) {
        await this.service.update(id, data);
        this.modal.show("Reserva actualizada correctamente");
      } else {
        await this.service.create(data);
        this.modal.show("Reserva creada correctamente");
      }

      this.form.reset();
      this.form.id.value = "";
      await this.loadReservas();
    } catch (err) {
      this.modal.show(err.message, "Error");
    }
  }

  onReset() {
    this.form.id.value = "";
    this.clearErrors();
  }

  async completar(id) {
    try {
      await this.service.completar(id);
      this.modal.show("Reserva completada");
      await this.loadReservas();
    } catch (err) {
      this.modal.show(err.message, "Error");
    }
  }

  async cancelar(id) {
    try {
      await this.service.cancelar(id);
      this.modal.show("Reserva cancelada");
      await this.loadReservas();
    } catch (err) {
      this.modal.show(err.message, "Error");
    }
  }

  async delete(id) {
    if (!confirm("¿Seguro que deseas borrar esta reserva?")) return;

    try {
      await this.service.delete(id);
      this.modal.show("Reserva borrada correctamente");
      await this.loadReservas();
    } catch (err) {
      this.modal.show(err.message, "Error");
    }
  }
}