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

const resetClienteFormState = () => {
  appState.clienteEditando = null;
  clienteForm?.reset();
  clearClienteValidation();
  updateClienteSubmitLabel();
};

const registrarCliente = async () => {
  const data = getClienteFormData();

  try {
    const created = await apiPost("/clientes", data);
    appState.clientes.push(normalizarCliente(created));
    renderClientes();
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