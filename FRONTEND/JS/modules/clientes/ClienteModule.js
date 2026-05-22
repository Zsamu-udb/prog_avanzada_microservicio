class ClienteModule {
  constructor(options) {
    this.modal = options.modal;
    this.service = new ClienteService();
    this.api = new ApiClient();
    this.clientes = [];
    this.onChange = null;

    this.form = document.getElementById("clienteForm");
    this.tableBody = document.querySelector("#clientesTabla tbody");
    this.btnRefrescar = document.getElementById("btnRefrescarClientes");

    this.form.addEventListener("submit", (e) => this.onSubmit(e));
    this.form.addEventListener("reset", () => this.onReset());
    this.btnRefrescar.addEventListener("click", () => this.loadClientes());
  }

  async loadClientes() {
    const data = await this.service.getAll();
    this.clientes = data || [];
    this.renderTable();
    if (this.onChange) this.onChange();
  }

  renderTable() {
    this.tableBody.innerHTML = "";

    if (this.clientes.length === 0) {
      const tr = document.createElement("tr");
      tr.innerHTML = '<td colspan="5" class="table__empty">Sin clientes registrados</td>';
      this.tableBody.appendChild(tr);
      return;
    }

    this.clientes.forEach((c) => {
      const tr = document.createElement("tr");
      tr.innerHTML = `
        <td>${c.nombre || "-"}</td>
        <td>${c.telefono || "-"}</td>
        <td>${c.correo || "-"}</td>
        <td>${c.numero_licencia || "-"}</td>
        <td>
          <button class="btn-soft" data-action="historial">Historial</button>
          <button class="btn-soft" data-action="edit">Editar</button>
          <button class="btn-soft" data-action="delete">Borrar</button>
        </td>
      `;

      tr.querySelector('[data-action="historial"]').addEventListener("click", () => this.showHistorial(c));
      tr.querySelector('[data-action="edit"]').addEventListener("click", () => this.fillForm(c));
      tr.querySelector('[data-action="delete"]').addEventListener("click", () => this.delete(c.id));

      this.tableBody.appendChild(tr);
    });
  }

  fillForm(cliente) {
    this.form.id.value = cliente.id || "";
    this.form.nombre.value = cliente.nombre || "";
    this.form.telefono.value = cliente.telefono || "";
    this.form.correo.value = cliente.correo || "";
    this.form.numero_licencia.value = cliente.numero_licencia || "";
  }

  clearErrors() {
    this.form.querySelectorAll(".field__error").forEach((span) => {
      span.textContent = "";
    });
  }

  setError(field, message) {
    const span = this.form.querySelector(`[data-error-for="${field}"]`);
    if (span) span.textContent = message;
  }

  getFormData() {
    return {
      nombre: this.form.nombre.value.trim(),
      telefono: this.form.telefono.value.trim(),
      correo: this.form.correo.value.trim(),
      numero_licencia: this.form.numero_licencia.value.trim()
    };
  }

  validate(data) {
    this.clearErrors();
    let ok = true;

    if (!Validator.required(data.nombre)) {
      this.setError("nombre", "El nombre es obligatorio");
      ok = false;
    }

    if (!Validator.email(data.correo)) {
      this.setError("correo", "El correo no es válido");
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
        this.modal.show("Cliente actualizado correctamente", "Información");
      } else {
        await this.service.create(data);
        this.modal.show("Cliente creado correctamente", "Información");
      }

      this.form.reset();
      this.form.id.value = "";
      await this.loadClientes();
    } catch (err) {
      this.modal.show(err.message, "Error");
    }
  }

  onReset() {
    this.form.id.value = "";
    this.clearErrors();
  }

  async delete(id) {
    if (!confirm("¿Seguro que deseas borrar este cliente?")) return;

    try {
      await this.service.delete(id);
      this.modal.show("Cliente borrado correctamente", "Información");
      await this.loadClientes();
    } catch (err) {
      this.modal.show(err.message, "Error");
    }
  }

  async showHistorial(cliente) {
    try {
      const data = await this.api.get(`/clientes/${cliente.id}/reservas`);
      const reservas = data.reservas || data || [];

      if (!reservas.length) {
        this.modal.show(`El cliente ${cliente.nombre} no tiene reservas.`, "Historial");
        return;
      }

      const lineas = reservas.map((r) => {
        const vehiculo = r.vehiculo ? `${r.vehiculo.marca} ${r.vehiculo.modelo}` : r.vehiculo_id;
        return `${r.fecha_inicio} a ${r.fecha_fin} - ${r.estado} - Vehículo: ${vehiculo}`;
      }).join(" | ");

      this.modal.show(lineas, `Historial de ${cliente.nombre}`);
    } catch (err) {
      this.modal.show(err.message, "Error al consultar historial");
    }
  }
}