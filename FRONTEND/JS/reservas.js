const reservaClienteSelect = document.getElementById("reservaCliente");
const reservaVehiculoSelect = document.getElementById("reservaVehiculo");
const reservasList = document.getElementById("reservasList");

const normalizarReserva = (item) => ({
  id: item.id,
  cliente_id: Number(item.cliente_id ?? item.clienteId ?? ""),
  vehiculo_id: Number(item.vehiculo_id ?? item.vehiculoId ?? ""),
  fecha_inicio: item.fecha_inicio ?? item.fechaInicio ?? "",
  fecha_fin: item.fecha_fin ?? item.fechaFin ?? "",
  estado: item.estado ?? "activa",
});

const getClienteById = (id) =>
  appState.clientes.find((cliente) => Number(cliente.id) === Number(id));

const getVehiculoById = (id) =>
  appState.vehiculos.find((vehiculo) => Number(vehiculo.id) === Number(id));

const formatFecha = (value = "") => {
  if (!value) return "Sin fecha";

  const fecha = new Date(`${value}T00:00:00`);
  if (Number.isNaN(fecha.getTime())) return value;

  return fecha.toLocaleDateString("es-CO", {
    year: "numeric",
    month: "short",
    day: "numeric",
  });
};

const actualizarSelectClientesReserva = () => {
  if (!reservaClienteSelect) return;

  const currentValue = reservaClienteSelect.value;

  reservaClienteSelect.innerHTML = `<option value="">Selecciona un cliente</option>`;

  appState.clientes.forEach((cliente) => {
    const option = document.createElement("option");
    option.value = cliente.id;
    option.textContent = `${cliente.nombre} · ${cliente.numero_licencia || "Sin licencia"}`;
    reservaClienteSelect.appendChild(option);
  });

  if (
    currentValue &&
    [...reservaClienteSelect.options].some(
      (option) => String(option.value) === String(currentValue)
    )
  ) {
    reservaClienteSelect.value = currentValue;
  }
};

const actualizarSelectVehiculosReserva = () => {
  if (!reservaVehiculoSelect) return;

  const currentValue = reservaVehiculoSelect.value;

  reservaVehiculoSelect.innerHTML = `<option value="">Selecciona un vehículo</option>`;

  appState.vehiculos.forEach((vehiculo) => {
    const option = document.createElement("option");
    option.value = vehiculo.id;
    option.textContent = `${vehiculo.marca} ${vehiculo.modelo} · ${vehiculo.estado}`;
    reservaVehiculoSelect.appendChild(option);
  });

  if (
    currentValue &&
    [...reservaVehiculoSelect.options].some(
      (option) => String(option.value) === String(currentValue)
    )
  ) {
    reservaVehiculoSelect.value = currentValue;
  }
};

const renderReservas = () => {
  if (!reservasList) return;

  reservasList.innerHTML = "";

  if (appState.reservas.length === 0) {
    reservasList.appendChild(
      createEmptyState(
        "Aún no hay reservas registradas",
        "Aquí aparecerán las reservas con su cliente, vehículo, fechas y estado."
      )
    );
    updateDashboardCounts();
    return;
  }

  appState.reservas.forEach((reserva) => {
    const cliente = getClienteById(reserva.cliente_id);
    const vehiculo = getVehiculoById(reserva.vehiculo_id);
    const statusMeta = getReservationStatusMeta(reserva.estado);

    const vehiculoTitulo = vehiculo
      ? `${vehiculo.marca} ${vehiculo.modelo}`
      : "Vehículo no disponible";

    const vehiculoCategoria = vehiculo?.categoria || "Sin categoría";
    const clienteNombre = cliente?.nombre || "Cliente no encontrado";
    const clienteLicencia = cliente?.numero_licencia || "Sin licencia";
    const imageSrc = vehiculo?.imagen || getVehicleImageByCategory(vehiculoCategoria);

    const article = document.createElement("article");
    article.className = "reservation-item";

    article.innerHTML = `
      <img
        src="${escapeHtml(imageSrc)}"
        alt="Vehículo asociado a la reserva ${escapeHtml(vehiculoTitulo)}"
        width="1200"
        height="800"
        loading="lazy"
      />

      <div class="reservation-item__body">
        <div class="reservation-item__head">
          <div class="reservation-item__title-wrap">
            <h5 class="reservation-item__title">${escapeHtml(vehiculoTitulo)}</h5>
            <span class="reservation-item__subtitle">
              ${escapeHtml(clienteNombre)} · ${escapeHtml(clienteLicencia)}
            </span>
          </div>

          <span class="status-badge ${statusMeta.className}">
            ${escapeHtml(statusMeta.label)}
          </span>
        </div>

        <div class="reservation-item__grid">
          <div class="reservation-meta">
            <span class="reservation-meta__label">Cliente</span>
            <span class="reservation-meta__value">${escapeHtml(clienteNombre)}</span>
          </div>

          <div class="reservation-meta">
            <span class="reservation-meta__label">Vehículo</span>
            <span class="reservation-meta__value">${escapeHtml(vehiculoTitulo)}</span>
          </div>

          <div class="reservation-meta">
            <span class="reservation-meta__label">Inicio</span>
            <span class="reservation-meta__value">${escapeHtml(formatFecha(reserva.fecha_inicio))}</span>
          </div>

          <div class="reservation-meta">
            <span class="reservation-meta__label">Fin</span>
            <span class="reservation-meta__value">${escapeHtml(formatFecha(reserva.fecha_fin))}</span>
          </div>
        </div>

        <div class="reservation-item__actions"></div>
      </div>
    `;

    const actionsWrap = article.querySelector(".reservation-item__actions");

    const editButton = createActionButton({
      text: "Editar",
      className: "action-btn--edit",
      title: `Editar reserva ${reserva.id}`,
      onClick: () => editarReserva(reserva.id),
    });

    const deleteButton = createActionButton({
      text: "Borrar",
      className: "action-btn--delete",
      title: `Borrar reserva ${reserva.id}`,
      onClick: () => borrarReserva(reserva.id),
    });

    actionsWrap.appendChild(editButton);
    actionsWrap.appendChild(deleteButton);

    if (slugifyText(reserva.estado) === "activa") {
      const completeButton = createActionButton({
        text: "Completar",
        className: "action-btn--success",
        title: `Completar reserva ${reserva.id}`,
        onClick: () => completarReserva(reserva.id),
      });

      const cancelButton = createActionButton({
        text: "Cancelar",
        className: "action-btn--warn",
        title: `Cancelar reserva ${reserva.id}`,
        onClick: () => cancelarReserva(reserva.id),
      });

      actionsWrap.appendChild(completeButton);
      actionsWrap.appendChild(cancelButton);
    }

    reservasList.appendChild(article);
  });

  updateDashboardCounts();
};

const consultarReservas = async () => {
  try {
    const data = await apiGet("/reservas");
    appState.reservas = Array.isArray(data) ? data.map(normalizarReserva) : [];
    renderReservas();
  } catch (error) {
    console.error(error);
    showModal("No fue posible cargar las reservas.", "error");
  }
};

const editarReserva = (id) => {
  const reserva = appState.reservas.find((item) => Number(item.id) === Number(id));

  if (!reserva) {
    showModal("No se encontró la reserva seleccionada.", "error");
    return;
  }

  appState.reservaEditando = reserva;
  fillReservaForm(reserva);
  activateTab("tab-reservas");
  document.getElementById("reservaCliente")?.focus();
};

const borrarReserva = async (id) => {
  const confirmado = window.confirm("¿Deseas borrar esta reserva?");
  if (!confirmado) return;

  try {
    await apiDelete(`/reservas/${id}`);
    appState.reservas = appState.reservas.filter(
      (item) => Number(item.id) !== Number(id)
    );
    renderReservas();

    // Igual que en clientes: reset del formulario + limpiar estado
    reservaForm?.reset();
    resetReservaFormState();

    showModal("Reserva eliminada correctamente.");
  } catch (error) {
    console.error(error);
    showModal(error.message || "No fue posible borrar la reserva.", "error");
  }
};

const completarReserva = async (id) => {
  try {
    const updated = await apiPatch(`/reservas/${id}/completar`, {});
    const normalized = normalizarReserva(updated);

    appState.reservas = appState.reservas.map((item) =>
      Number(item.id) === Number(id) ? normalized : item
    );

    renderReservas();
    showModal("Reserva completada correctamente.");
  } catch (error) {
    console.error(error);
    showModal(error.message || "No fue posible completar la reserva.", "error");
  }
};

const cancelarReserva = async (id) => {
  try {
    const updated = await apiPatch(`/reservas/${id}/cancelar`, {});
    const normalized = normalizarReserva(updated);

    appState.reservas = appState.reservas.map((item) =>
      Number(item.id) === Number(id) ? normalized : item
    );

    renderReservas();
    showModal("Reserva cancelada correctamente.");
  } catch (error) {
    console.error(error);
    showModal(error.message || "No fue posible cancelar la reserva.", "error");
  }
};