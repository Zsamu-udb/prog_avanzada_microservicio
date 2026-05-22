class VehiculoModule {
  constructor(options) {
    this.modal = options.modal;
    this.service = new VehiculoService();
    this.api = new ApiClient();
    this.vehiculos = [];
    this.soloDisponibles = false;
    this.onChange = null;

    this.form = document.getElementById("vehiculoForm");
    this.tableBody = document.querySelector("#vehiculosTabla tbody");
    this.btnRefrescar = document.getElementById("btnRefrescarVehiculos");
    this.btnFiltrarDisponibles = document.getElementById("btnFiltrarDisponibles");

    this.form.addEventListener("submit", (e) => this.onSubmit(e));
    this.form.addEventListener("reset", () => this.onReset());
    this.btnRefrescar.addEventListener("click", () => this.loadVehiculos());

    this.btnFiltrarDisponibles.addEventListener("click", () => {
      this.soloDisponibles = !this.soloDisponibles;
      this.btnFiltrarDisponibles.textContent = this.soloDisponibles ? "Todos" : "Solo disponibles";
      this.renderTable();
    });
  }

  async loadVehiculos() {
    const data = await this.service.getAll();
    this.vehiculos = data || [];
    this.renderTable();
    if (this.onChange) this.onChange();
  }

  getFiltrados() {
    if (!this.soloDisponibles) return this.vehiculos;
    return this.vehiculos.filter((v) => v.estado === "disponible");
  }

  renderTable() {
    const lista = this.getFiltrados();
    this.tableBody.innerHTML = "";

    if (lista.length === 0) {
      const tr = document.createElement("tr");
      tr.innerHTML = '<td colspan="6" class="table__empty">Sin vehículos registrados</td>';
      this.tableBody.appendChild(tr);
      return;
    }

    lista.forEach((v) => {
      const tr = document.createElement("tr");
      tr.innerHTML = `
        <td>${v.marca || "-"}</td>
        <td>${v.modelo || "-"}</td>
        <td>${v.anio || "-"}</td>
        <td>${v.categoria || "-"}</td>
        <td>${v.estado || "-"}</td>
        <td>
          <button class="btn-soft" data-action="historial">Historial</button>
          <button class="btn-soft" data-action="edit">Editar</button>
          <button class="btn-soft" data-action="estado">Cambiar estado</button>
          <button class="btn-soft" data-action="delete">Borrar</button>
        </td>
      `;

      tr.querySelector('[data-action="historial"]').addEventListener("click", () => this.showHistorialVehiculo(v));
      tr.querySelector('[data-action="edit"]').addEventListener("click", () => this.fillForm(v));
      tr.querySelector('[data-action="estado"]').addEventListener("click", () => this.changeEstadoPrompt(v));
      tr.querySelector('[data-action="delete"]').addEventListener("click", () => this.delete(v.id));

      this.tableBody.appendChild(tr);
    });
  }

  fillForm(v) {
    this.form.id.value = v.id || "";
    this.form.marca.value = v.marca || "";
    this.form.modelo.value = v.modelo || "";
    this.form.anio.value = v.anio || "";
    this.form.categoria.value = v.categoria || "";
    this.form.estado.value = v.estado || "disponible";
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
      marca: this.form.marca.value.trim(),
      modelo: this.form.modelo.value.trim(),
      anio: this.form.anio.value.trim(),
      categoria: this.form.categoria.value.trim(),
      estado: this.form.estado.value
    };
  }

  validate(data) {
    this.clearErrors();
    let ok = true;

    if (!Validator.required(data.marca)) {
      this.setError("marca", "La marca es obligatoria");
      ok = false;
    }

    if (!Validator.required(data.modelo)) {
      this.setError("modelo", "El modelo es obligatorio");
      ok = false;
    }

    if (!Validator.positiveInt(data.anio)) {
      this.setError("anio", "El año no es válido");
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
        this.modal.show("Vehículo actualizado correctamente", "Información");
      } else {
        await this.service.create(data);
        this.modal.show("Vehículo creado correctamente", "Información");
      }

      this.form.reset();
      this.form.id.value = "";
      await this.loadVehiculos();
    } catch (err) {
      this.modal.show(err.message, "Error");
    }
  }

  onReset() {
    this.form.id.value = "";
    this.clearErrors();
  }

  async changeEstadoPrompt(v) {
    const nuevo = prompt("Estado: disponible, alquilado, mantenimiento", v.estado || "disponible");
    if (!nuevo) return;

    try {
      await this.service.changeEstado(v.id, nuevo);
      this.modal.show("Estado actualizado", "Información");
      await this.loadVehiculos();
    } catch (err) {
      this.modal.show(err.message, "Error");
    }
  }

  async delete(id) {
    if (!confirm("¿Seguro que deseas borrar este vehículo?")) return;

    try {
      await this.service.delete(id);
      this.modal.show("Vehículo borrado correctamente", "Información");
      await this.loadVehiculos();
    } catch (err) {
      this.modal.show(err.message, "Error");
    }
  }

  async showHistorialVehiculo(v) {
    try {
      const data = await this.api.get(`/vehiculos/${v.id}/reservas`);
      const reservas = data.reservas || data || [];

      if (!reservas.length) {
        this.modal.show(`El vehículo ${v.marca} ${v.modelo} no tiene reservas.`, "Historial");
        return;
      }

      const lineas = reservas.map((r) => {
        const cliente = r.cliente ? r.cliente.nombre : r.cliente_id;
        return `${r.fecha_inicio} a ${r.fecha_fin} - ${r.estado} - Cliente: ${cliente}`;
      }).join(" | ");

      this.modal.show(lineas, `Historial de ${v.marca} ${v.modelo} ${v.anio}`);
    } catch (err) {
      this.modal.show(err.message, "Error al consultar historial");
    }
  }
}