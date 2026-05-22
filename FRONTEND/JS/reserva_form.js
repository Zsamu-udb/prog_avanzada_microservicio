const reservaForm = document.forms["reservaForm"];
const reservaClienteInput = document.getElementById("reservaCliente");
const reservaVehiculoInput = document.getElementById("reservaVehiculo");
const reservaFechaInicioInput = document.getElementById("reservaFechaInicio");
const reservaFechaFinInput = document.getElementById("reservaFechaFin");
const reservaEstadoInput = document.getElementById("reservaEstado");

const msgReservaCliente = document.getElementById("msgReservaCliente");
const msgReservaVehiculo = document.getElementById("msgReservaVehiculo");
const msgReservaFechaInicio = document.getElementById("msgReservaFechaInicio");
const msgReservaFechaFin = document.getElementById("msgReservaFechaFin");

const reservaSubmitButton = reservaForm?.querySelector("button[type='submit']");

const getReservaFormData = () => ({
  cliente_id: reservaClienteInput?.value || "",
  vehiculo_id: reservaVehiculoInput?.value || "",
  fecha_inicio: reservaFechaInicioInput?.value || "",
  fecha_fin: reservaFechaFinInput?.value || "",
  estado: reservaEstadoInput?.value || "activa",
});

const fillReservaForm = (reserva) => {
  if (!reservaForm) return;

  actualizarSelectClientesReserva();
  actualizarSelectVehiculosReserva();

  reservaClienteInput.value = reserva.cliente_id ?? "";
  reservaVehiculoInput.value = reserva.vehiculo_id ?? "";
  reservaFechaInicioInput.value = reserva.fecha_inicio ?? "";
  reservaFechaFinInput.value = reserva.fecha_fin ?? "";
  reservaEstadoInput.value = reserva.estado ?? "activa";

  updateReservaSubmitLabel();
};

const clearReservaValidation = () => {
  if (msgReservaCliente) msgReservaCliente.style.display = "none";
  if (msgReservaVehiculo) msgReservaVehiculo.style.display = "none";
  if (msgReservaFechaInicio) msgReservaFechaInicio.style.display = "none";
  if (msgReservaFechaFin) msgReservaFechaFin.style.display = "none";
};

const validateReservaForm = () => {
  const data = getReservaFormData();
  let isValid = true;

  clearReservaValidation();

  if (!data.cliente_id) {
    msgReservaCliente.style.display = "block";
    isValid = false;
  }

  if (!data.vehiculo_id) {
    msgReservaVehiculo.style.display = "block";
    isValid = false;
  }

  if (!data.fecha_inicio) {
    msgReservaFechaInicio.style.display = "block";
    isValid = false;
  }

  if (!data.fecha_fin) {
    msgReservaFechaFin.style.display = "block";
    isValid = "false";
  }

  if (
    data.fecha_inicio &&
    data.fecha_fin &&
    new Date(data.fecha_fin) < new Date(data.fecha_inicio)
  ) {
    msgReservaFechaFin.style.display = "block";
    msgReservaFechaFin.textContent = "La fecha final no puede ser menor que la inicial.";
    isValid = false;
  } else if (msgReservaFechaFin) {
    msgReservaFechaFin.textContent = "La fecha final es obligatoria.";
  }

  return isValid;
};

const updateReservaSubmitLabel = () => {
  if (!reservaSubmitButton) return;

  reservaSubmitButton.textContent = appState.reservaEditando
    ? "Actualizar reserva"
    : "Guardar reserva";
};

const resetReservaFormState = () => {
  appState.reservaEditando = null;
  reservaForm?.reset();
  clearReservaValidation();
  reservaEstadoInput.value = "activa";
  updateReservaSubmitLabel();
};

const registrarReserva = async () => {
  const data = getReservaFormData();

  try {
    const created = await apiPost("/reservas", data);
    appState.reservas.push(normalizarReserva(created));
    renderReservas();
    resetReservaFormState();
    showModal("Reserva guardada correctamente.");
  } catch (error) {
    console.error(error);
    showModal(error.message || "No fue posible guardar la reserva.", "error");
  }
};

const actualizarReserva = async () => {
  const data = getReservaFormData();
  const reservaId = appState.reservaEditando?.id;

  if (!reservaId) {
    showModal("No hay una reserva seleccionada para actualizar.", "error");
    return;
  }

  try {
    const updated = await apiPut(`/reservas/${reservaId}`, data);
    const normalized = normalizarReserva(updated);

    appState.reservas = appState.reservas.map((item) =>
      Number(item.id) === Number(reservaId) ? normalized : item
    );

    renderReservas();
    resetReservaFormState();
    showModal("Reserva actualizada correctamente.");
  } catch (error) {
    console.error(error);
    showModal(error.message || "No fue posible actualizar la reserva.", "error");
  }
};

reservaForm?.addEventListener("submit", async (event) => {
  event.preventDefault();

  const isValid = validateReservaForm();
  if (!isValid) {
    showModal("Revisa los campos obligatorios de la reserva.", "warning");
    return;
  }

  if (appState.reservaEditando) {
    await actualizarReserva();
  } else {
    await registrarReserva();
  }
});

reservaForm?.addEventListener("reset", () => {
  setTimeout(() => {
    resetReservaFormState();
  }, 0);
});

reservaClienteInput?.addEventListener("change", () => {
  if (reservaClienteInput.value) msgReservaCliente.style.display = "none";
});

reservaVehiculoInput?.addEventListener("change", () => {
  if (reservaVehiculoInput.value) msgReservaVehiculo.style.display = "none";
});

reservaFechaInicioInput?.addEventListener("change", () => {
  if (reservaFechaInicioInput.value) msgReservaFechaInicio.style.display = "none";
});

reservaFechaFinInput?.addEventListener("change", () => {
  if (reservaFechaFinInput.value) {
    msgReservaFechaFin.style.display = "none";
    msgReservaFechaFin.textContent = "La fecha final es obligatoria.";
  }
});

updateReservaSubmitLabel();