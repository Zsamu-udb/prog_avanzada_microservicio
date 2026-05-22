const vehiculoForm = document.forms["vehiculoForm"];
const vehiculoMarcaInput = document.getElementById("vehiculoMarca");
const vehiculoModeloInput = document.getElementById("vehiculoModelo");
const vehiculoAnioInput = document.getElementById("vehiculoAnio");
const vehiculoCategoriaInput = document.getElementById("vehiculoCategoria");
const vehiculoEstadoInput = document.getElementById("vehiculoEstado");

const msgVehiculoMarca = document.getElementById("msgVehiculoMarca");
const msgVehiculoModelo = document.getElementById("msgVehiculoModelo");
const msgVehiculoAnio = document.getElementById("msgVehiculoAnio");
const msgVehiculoCategoria = document.getElementById("msgVehiculoCategoria");

const vehiculoSubmitButton = vehiculoForm?.querySelector("button[type='submit']");

const getVehiculoFormData = () => ({
  marca: vehiculoMarcaInput?.value.trim() || "",
  modelo: vehiculoModeloInput?.value.trim() || "",
  anio: vehiculoAnioInput?.value.trim() || "",
  categoria: vehiculoCategoriaInput?.value.trim() || "",
  estado: vehiculoEstadoInput?.value || "disponible",
});

const fillVehiculoForm = (vehiculo) => {
  if (!vehiculoForm) return;

  appState.vehiculoEditando = vehiculo;

  vehiculoMarcaInput.value = vehiculo.marca ?? "";
  vehiculoModeloInput.value = vehiculo.modelo ?? "";
  vehiculoAnioInput.value = vehiculo.anio ?? "";
  vehiculoCategoriaInput.value = vehiculo.categoria ?? "";
  vehiculoEstadoInput.value = vehiculo.estado ?? "disponible";

  clearVehiculoValidation();
  updateVehiculoSubmitLabel();
};

const clearVehiculoValidation = () => {
  if (msgVehiculoMarca) msgVehiculoMarca.style.display = "none";
  if (msgVehiculoModelo) msgVehiculoModelo.style.display = "none";
  if (msgVehiculoAnio) msgVehiculoAnio.style.display = "none";
  if (msgVehiculoCategoria) msgVehiculoCategoria.style.display = "none";
};

const validateVehiculoForm = () => {
  const data = getVehiculoFormData();
  let isValid = true;

  clearVehiculoValidation();

  if (!data.marca) {
    if (msgVehiculoMarca) msgVehiculoMarca.style.display = "block";
    isValid = false;
  }

  if (!data.modelo) {
    if (msgVehiculoModelo) msgVehiculoModelo.style.display = "block";
    isValid = false;
  }

  if (!data.anio) {
    if (msgVehiculoAnio) msgVehiculoAnio.style.display = "block";
    isValid = false;
  }

  if (!data.categoria) {
    if (msgVehiculoCategoria) msgVehiculoCategoria.style.display = "block";
    isValid = false;
  }

  return isValid;
};

const updateVehiculoSubmitLabel = () => {
  if (!vehiculoSubmitButton) return;

  vehiculoSubmitButton.textContent = appState.vehiculoEditando
    ? "Actualizar vehículo"
    : "Guardar vehículo";
};

const resetVehiculoFormState = () => {
  appState.vehiculoEditando = null;

  if (vehiculoForm) {
    vehiculoForm.reset();
  }

  clearVehiculoValidation();

  if (vehiculoEstadoInput) {
    vehiculoEstadoInput.value = "disponible";
  }

  updateVehiculoSubmitLabel();

  setTimeout(() => {
    vehiculoMarcaInput?.focus();
  }, 0);
};

const syncVehiculoCreated = async (fallbackData) => {
  try {
    await consultarVehiculos();
  } catch (error) {
    console.error(error);
    appState.vehiculos.push(normalizarVehiculo(fallbackData));
    renderVehiculos();
  }
};

const registrarVehiculo = async () => {
  const data = getVehiculoFormData();

  try {
    const created = await apiPost("/vehiculos", data);

    if (created && typeof created === "object" && created.id !== undefined) {
      appState.vehiculos.push(normalizarVehiculo(created));
      renderVehiculos();
    } else {
      await syncVehiculoCreated(data);
    }

    resetVehiculoFormState();
    showModal("Vehículo guardado correctamente.");
  } catch (error) {
    console.error(error);
    showModal(error.message || "No fue posible guardar el vehículo.", "error");
  }
};

const actualizarVehiculo = async () => {
  const data = getVehiculoFormData();
  const vehiculoId = appState.vehiculoEditando?.id;

  if (!vehiculoId) {
    showModal("No hay un vehículo seleccionado para actualizar.", "error");
    return;
  }

  try {
    const updated = await apiPut(`/vehiculos/${vehiculoId}`, data);

    if (updated && typeof updated === "object" && updated.id !== undefined) {
      const normalized = normalizarVehiculo(updated);

      appState.vehiculos = appState.vehiculos.map((item) =>
        Number(item.id) === Number(vehiculoId) ? normalized : item
      );

      renderVehiculos();
    } else {
      await consultarVehiculos();
    }

    resetVehiculoFormState();
    showModal("Vehículo actualizado correctamente.");
  } catch (error) {
    console.error(error);
    showModal(error.message || "No fue posible actualizar el vehículo.", "error");
  }
};

vehiculoForm?.addEventListener("submit", async (event) => {
  event.preventDefault();

  const isValid = validateVehiculoForm();

  if (!isValid) {
    showModal("Revisa los campos obligatorios del vehículo.", "warning");
    return;
  }

  if (appState.vehiculoEditando) {
    await actualizarVehiculo();
  } else {
    await registrarVehiculo();
  }
});

vehiculoForm?.addEventListener("reset", () => {
  setTimeout(() => {
    resetVehiculoFormState();
  }, 0);
});

vehiculoMarcaInput?.addEventListener("input", () => {
  if (vehiculoMarcaInput.value.trim() && msgVehiculoMarca) {
    msgVehiculoMarca.style.display = "none";
  }
});

vehiculoModeloInput?.addEventListener("input", () => {
  if (vehiculoModeloInput.value.trim() && msgVehiculoModelo) {
    msgVehiculoModelo.style.display = "none";
  }
});

vehiculoAnioInput?.addEventListener("input", () => {
  if (vehiculoAnioInput.value.trim() && msgVehiculoAnio) {
    msgVehiculoAnio.style.display = "none";
  }
});

vehiculoCategoriaInput?.addEventListener("input", () => {
  if (vehiculoCategoriaInput.value.trim() && msgVehiculoCategoria) {
    msgVehiculoCategoria.style.display = "none";
  }
});

updateVehiculoSubmitLabel();