const clientesTable = document.getElementById("clientesTable");
const clientesTableBody = clientesTable?.querySelector("tbody");

const normalizarCliente = (item) => ({
  id: item.id,
  nombre: item.nombre ?? "",
  telefono: item.telefono ?? "",
  correo: item.correo ?? item.email ?? "",
  numero_licencia: item.numero_licencia ?? item.licencia ?? "",
});

const renderClientes = () => {
  if (!clientesTableBody) return;

  clientesTableBody.innerHTML = "";

  if (appState.clientes.length === 0) {
    const emptyRow = document.createElement("tr");
    emptyRow.innerHTML = `<td colspan="5">No hay clientes registrados todavía.</td>`;
    clientesTableBody.appendChild(emptyRow);
    updateDashboardCounts();
    return;
  }

  appState.clientes.forEach((cliente) => {
    const tr = document.createElement("tr");

    tr.innerHTML = `
      <td>${escapeHtml(cliente.nombre)}</td>
      <td>${escapeHtml(cliente.telefono)}</td>
      <td>${escapeHtml(cliente.correo)}</td>
      <td>${escapeHtml(cliente.numero_licencia)}</td>
      <td></td>
    `;

    const actionsCell = tr.querySelector("td:last-child");
    const actionsWrap = document.createElement("div");
    actionsWrap.className = "table-actions";

    const editButton = createActionButton({
      text: "Editar",
      className: "action-btn--edit",
      title: `Editar cliente ${cliente.nombre}`,
      onClick: () => editarCliente(cliente.id),
    });

    const deleteButton = createActionButton({
      text: "Borrar",
      className: "action-btn--delete",
      title: `Borrar cliente ${cliente.nombre}`,
      onClick: () => borrarCliente(cliente.id),
    });

    actionsWrap.appendChild(editButton);
    actionsWrap.appendChild(deleteButton);
    actionsCell.appendChild(actionsWrap);

    clientesTableBody.appendChild(tr);
  });

  actualizarSelectClientesReserva();
  updateDashboardCounts();
};

const consultarClientes = async () => {
  try {
    const data = await apiGet("/clientes");
    appState.clientes = Array.isArray(data) ? data.map(normalizarCliente) : [];
    renderClientes();
  } catch (error) {
    console.error(error);
    showModal("No fue posible cargar los clientes.", "error");
  }
};

const editarCliente = (id) => {
  const cliente = appState.clientes.find((item) => Number(item.id) === Number(id));

  if (!cliente) {
    showModal("No se encontró el cliente seleccionado.", "error");
    return;
  }

  appState.clienteEditando = cliente;
  fillClienteForm(cliente);
  activateTab("tab-clientes");
  document.getElementById("clienteNombre")?.focus();
};

const borrarCliente = async (id) => {
  const confirmado = window.confirm("¿Deseas borrar este cliente?");
  if (!confirmado) return;

  try {
    await apiDelete(`/clientes/${id}`);
    appState.clientes = appState.clientes.filter(
      (item) => Number(item.id) !== Number(id)
    );
    renderClientes();

    // IMPORTANTE: resetear formulario y luego limpiar estado
    clienteForm?.reset();
    resetClienteFormState();

    showModal("Cliente eliminado correctamente.");
  } catch (error) {
    console.error(error);
    showModal(error.message || "No fue posible borrar el cliente.", "error");
  }
};

const clienteForm = document.forms["clienteForm"];
const clienteNombreInput = document.getElementById("clienteNombre");
const clienteTelefonoInput = document.getElementById("clienteTelefono");
const clienteCorreoInput = document.getElementById("clienteCorreo");
const clienteLicenciaInput = document.getElementById("clienteLicencia");

const msgClienteNombre = document.getElementById("msgClienteNombre");
const msgClienteCorreo = document.getElementById("msgClienteCorreo");

const clienteSubmitButton = clienteForm?.querySelector("button[type='submit']");

const getClienteFormData = () => ({
  nombre: clienteNombreInput?.value.trim() || "",
  telefono: clienteTelefonoInput?.value.trim() || "",
  correo: clienteCorreoInput?.value.trim() || "",
  numero_licencia: clienteLicenciaInput?.value.trim() || "",
});

const fillClienteForm = (cliente) => {
  if (!clienteForm) return;

  clienteNombreInput.value = cliente.nombre ?? "";
  clienteTelefonoInput.value = cliente.telefono ?? "";
  clienteCorreoInput.value = cliente.correo ?? "";
  clienteLicenciaInput.value = cliente.numero_licencia ?? "";

  updateClienteSubmitLabel();
};

const clearClienteValidation = () => {
  if (msgClienteNombre) msgClienteNombre.style.display = "none";
  if (msgClienteCorreo) msgClienteCorreo.style.display = "none";
};

const validarCorreo = (correo) => {
  if (!correo) return true;
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(correo);
};

const validateClienteForm = () => {
  const data = getClienteFormData();
  let isValid = true;

  clearClienteValidation();

  if (!data.nombre) {
    if (msgClienteNombre) msgClienteNombre.style.display = "block";
    isValid = false;
  }

  if (!validarCorreo(data.correo)) {
    if (msgClienteCorreo) msgClienteCorreo.style.display = "block";
    isValid = false;
  }

  return isValid;
};

const updateClienteSubmitLabel = () => {
  if (!clienteSubmitButton) return;

  clienteSubmitButton.textContent = appState.clienteEditando
    ? "Actualizar cliente"
    : "Guardar cliente";
};

// AHORA resetClienteFormState YA NO HACE reset() DEL FORM
const resetClienteFormState = () => {
  appState.clienteEditando = null;
  clearClienteValidation();
  updateClienteSubmitLabel();
};

const registrarCliente = async () => {
  const data = getClienteFormData();

  try {
    const created = await apiPost("/clientes", data);
    appState.clientes.push(normalizarCliente(created));
    renderClientes();

    // Resetear formulario y luego limpiar estado
    clienteForm?.reset();
    resetClienteFormState();

    showModal("Cliente guardado correctamente.");
  } catch (error) {
    console.error(error);
    showModal(error.message || "No fue posible guardar el cliente.", "error");
  }
};

const actualizarCliente = async () => {
  const data = getClienteFormData();
  const clienteId = appState.clienteEditando?.id;

  if (!clienteId) {
    showModal("No hay un cliente seleccionado para actualizar.", "error");
    return;
  }

  try {
    const updated = await apiPut(`/clientes/${clienteId}`, data);
    const normalized = normalizarCliente(updated);

    appState.clientes = appState.clientes.map((item) =>
      Number(item.id) === Number(clienteId) ? normalized : item
    );

    renderClientes();

    // Resetear formulario y luego limpiar estado
    clienteForm?.reset();
    resetClienteFormState();

    showModal("Cliente actualizado correctamente.");
  } catch (error) {
    console.error(error);
    showModal(error.message || "No fue posible actualizar el cliente.", "error");
  }
};

clienteForm?.addEventListener("submit", async (event) => {
  event.preventDefault();

  const isValid = validateClienteForm();
  if (!isValid) {
    showModal("Revisa los campos obligatorios del cliente.", "warning");
    return;
  }

  if (appState.clienteEditando) {
    await actualizarCliente();
  } else {
    await registrarCliente();
  }
});

// SE MANTIENE EL RESET, PERO YA NO CREA BUCLE
clienteForm?.addEventListener("reset", () => {
  setTimeout(() => {
    resetClienteFormState();
  }, 0);
});

clienteNombreInput?.addEventListener("input", () => {
  if (clienteNombreInput.value.trim()) {
    msgClienteNombre.style.display = "none";
  }
});

clienteCorreoInput?.addEventListener("input", () => {
  if (validarCorreo(clienteCorreoInput.value.trim())) {
    msgClienteCorreo.style.display = "none";
  }
});

updateClienteSubmitLabel();